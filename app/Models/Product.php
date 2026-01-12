<?php

declare(strict_types=1);

namespace App\Models;

use App\Helper\DateFormat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property int $categorie_id
 * @property string $reference
 * @property string $nom
 * @property int $prix
 * @property int $favoris
 * @property float $poids
 * @property int $stock
 * @property string|null $color
 * @property string|null $taille
 * @property string $resume
 * @property string|null $description
 * @property string|null $video
 * @property string $cover
 * @property string $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Category $categorie
 * @property-read string $prix_final
 * @property-read string $prix_format
 * @property-read string $prix_promo
 * @property-read int $reduction
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Image> $images
 * @property-read int|null $images_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Order> $orders
 * @property-read int|null $orders_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Promotion> $promotions
 * @property-read int|null $promotions_count
 *
 * @method static Builder|Product byStock()
 * @method static \Database\Factories\ProductFactory factory($count = null, $state = [])
 * @method static Builder|Product newModelQuery()
 * @method static Builder|Product newQuery()
 * @method static Builder|Product query()
 * @method static Builder|Product whereCategorieId($value)
 * @method static Builder|Product whereColor($value)
 * @method static Builder|Product whereCover($value)
 * @method static Builder|Product whereCreatedAt($value)
 * @method static Builder|Product whereDescription($value)
 * @method static Builder|Product whereFavoris($value)
 * @method static Builder|Product whereId($value)
 * @method static Builder|Product whereNom($value)
 * @method static Builder|Product wherePoids($value)
 * @method static Builder|Product wherePrix($value)
 * @method static Builder|Product whereReference($value)
 * @method static Builder|Product whereResume($value)
 * @method static Builder|Product whereStock($value)
 * @method static Builder|Product whereTaille($value)
 * @method static Builder|Product whereUpdatedAt($value)
 * @method static Builder|Product whereVideo($value)
 *
 * @property string|null $slug
 * @property bool $is_preorder
 * @property int $status
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Promotion> $activePromotion
 * @property-read int|null $active_promotion_count
 * @property-read int $prix_final_base
 *
 * @method static Builder<static>|Product active()
 * @method static Builder<static>|Product whereIsPreorder($value)
 * @method static Builder<static>|Product whereSlug($value)
 * @method static Builder<static>|Product whereStatus($value)
 *
 * @mixin \Eloquent
 */
final class Product extends Model
{
    use DateFormat;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['categorie_id', 'reference', 'nom', 'color', 'taille', 'description', 'resume', 'poids', 'video', 'prix', 'cover', 'stock', 'favoris', 'slug', 'is_preorder'];

    /**
     * Scope to get nature by structure.
     */
    public function scopeByStock(Builder $query): Builder
    {
        return $query->where('stock', '>=', 1);
    }

    public function scopeActive($query): Builder
    {
        return $query->where('is_preorder', 1);
    }

    public function getMontant(): string
    {
        $montant = $this->pivot->montant;
        $devise = session('devise');

        if ($devise === 'EUR') {
            $taux = session('taux_eur', 1);

            return number_format($montant / $taux, 2, ',', ' ').' €';
        }

        return number_format($montant, 0, ',', ' ').' CFA';
    }

    public function activePromotion()
    {
        return $this->belongsToMany(Promotion::class)
            ->where('etat', 'En cours')
            ->where('debut', '<=', now())
            ->where('fin', '>=', now())
            ->orderByDesc('id');
    }

    /* ---------- Prix & promotions ---------- */

    public function getPrixFinalBaseAttribute(): int
    {
        $prix = $this->prix;

        if ($promo = $this->resolveActivePromotion()) {
            $prix *= (1 - $promo->reduction / 100);
        }

        return $prix; // toujours en CFA brut
    }

    public function getReductionAttribute(): int
    {
        return $this->resolveActivePromotion()?->reduction ?? 0;
    }

    public function getPrixPromoAttribute()
    {
        $promo = $this->resolveActivePromotion();

        if (! $promo) {
            return $this->prix_format;
        }

        return $this->prix * (1 - $promo->reduction / 100);
    }

    public function getPrixFormatAttribute(): string
    {
        if (session('devise') === 'EUR') {
            $taux = session('taux_eur', 1);
            $prix = number_format($this->prix / $taux, 2, ',', ' ');

            return "{$prix} €";
        }

        $prix = number_format($this->prix, 0, ',', ' ');

        return "{$prix} CFA";
    }

    /**
     * Get the categorie that owns the Product
     */
    public function categorie(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * The promotions that belong to the Product
     */
    public function promotions(): BelongsToMany
    {
        return $this->belongsToMany(Promotion::class);
    }

    /**
     * The orders that belong to the Product
     */
    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class)->withPivot('quantity', 'montant');
    }

    public function getCoverAttribute(): string
    {
        if (! app()->isProduction()) {
            return asset('admin/assets/imgs/theme/logo.svg');
        }

        return Storage::url($this->attributes['cover']);
    }

    public function DocLink(): string
    {
        return Storage::url($this->attributes['cover']);
    }

    public function VideoLink(): string
    {
        return Storage::url($this->attributes['video']);
    }

    /**
     * Get all of the images for the Product
     */
    public function images(): HasMany
    {
        return $this->hasMany(Image::class);
    }

    // Automatically generate slug
    protected static function boot()
    {
        parent::boot();

        // Générer ou mettre à jour le slug avant la sauvegarde
        self::saving(function ($product) {
            // Vérifier si le champ 'nom' a été modifié
            if ($product->isDirty('nom')) {
                // Générer un slug de base
                $baseSlug = Str::slug($product->nom, '-');
                $slug = $baseSlug;
                $counter = 1;

                // Vérifier l'unicité du slug
                while (self::where('slug', $slug)->where('id', '!=', $product->id)->exists()) {
                    $slug = $baseSlug.'-'.$counter;
                    $counter++;
                }

                $product->slug = $slug;
            }
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'poids' => 'float',
            'stock' => 'integer',
            'favoris' => 'boolean',
            'is_preorder' => 'boolean',
        ];
    }

    // Helper interne : trouve la promo active SANS refaire de requêtes si déjà chargée
    private function resolveActivePromotion(): ?Promotion
    {
        return $this->activePromotion
            ->first();
    }
}
