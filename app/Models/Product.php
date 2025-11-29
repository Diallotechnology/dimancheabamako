<?php

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
 * @property-read \App\Models\Category $categorie
 * @property-read string $prix_final
 * @property-read string $prix_format
 * @property-read string $prix_promo
 * @property-read int $reduction
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Image> $images
 * @property-read int|null $images_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Order> $orders
 * @property-read int|null $orders_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Promotion> $promotions
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
 * @mixin \Eloquent
 */
class Product extends Model
{
    use DateFormat;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['categorie_id', 'reference', 'nom', 'color', 'taille', 'description', 'resume', 'poids', 'video', 'prix', 'cover', 'stock', 'favoris', 'slug'];

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = ['categorie'];

    /**
     * Scope to get nature by structure.
     */
    public function scopeByStock(Builder $query): Builder
    {
        return $query->where('stock', '>=', 1);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }


    // Automatically generate slug
    protected static function boot()
    {
        parent::boot();

        // Générer ou mettre à jour le slug avant la sauvegarde
        static::saving(function ($product) {
            // Vérifier si le champ 'nom' a été modifié
            if ($product->isDirty('nom')) {
                // Générer un slug de base
                $baseSlug = Str::slug($product->nom, '-');
                $slug = $baseSlug;
                $counter = 1;

                // Vérifier l'unicité du slug
                while (self::where('slug', $slug)->where('id', '!=', $product->id)->exists()) {
                    $slug = $baseSlug . '-' . $counter;
                    $counter++;
                }

                $product->slug = $slug;
            }
        });
    }

    public function getPrixFinal(): int
    {
        // Si une promotion est associée au produit, calculer le prix avec réduction
        if ($this->promotions->isNotEmpty()) {
            $promo = $this->promotions()->first();
            $prix = $this->prix * (1 - $promo->reduction / 100);

            // Retour du prix formaté avec devise
            return $prix;
        } else {
            return $this->prix;
        }
    }

    public function getMontant(): float|int
    {
        // Récupération du taux de conversion et du symbole de devise en fonction de la locale de la session
        if (session('devise') === 'EUR') {
            $tauxConversion = session('devise') === 'EUR' ? Devise::whereType('EUR')->value('taux') : '';

            return number_format($this->pivot->montant / $tauxConversion, 2);
        } elseif (session('devise') === 'CFA') {
            return number_format($this->pivot->montant);
        }
    }

    public function getPrixFinalAttribute(): string
    {
        // Récupération du taux de conversion et du symbole de devise en fonction de la locale de la session
        $tauxConversion = session('devise') === 'EUR' ? Devise::whereType('EUR')->value('taux') : '';
        // Conversion du prix en devise locale et formatage
        $deviseSymbole = session('devise') === 'EUR' ? '€' : 'CFA';
        // Si une promotion est associée au produit, calculer le prix avec réduction
        if ($this->promotions->isNotEmpty()) {
            $promo = $this->promotions()->first();
            $prix = $this->prix * (1 - $promo->reduction / 100);

            if (session('devise') === 'EUR') {
                // Conversion du prix en devise locale et formatage
                $prixFormat = number_format($prix / $tauxConversion, 2);
            } elseif (session('devise') === 'CFA') {
                $prixFormat = number_format($prix, 0, ',', ' ');
            }

            // Retour du prix formaté avec devise
            return $prixFormat . ' ' . $deviseSymbole;
        } else {
            if (session('devise') === 'EUR') {
                // Conversion du prix en devise locale et formatage
                $prixFormat = number_format($this->prix / $tauxConversion, 2);
            } elseif (session('devise') === 'CFA') {
                $prixFormat = number_format($this->prix, 0, ',', ' ');
            }

            return $prixFormat . ' ' . $deviseSymbole;
        }
    }

    public function getPrixPromoAttribute(): string
    {
        // Récupération du taux de conversion et du symbole de devise en fonction de la locale de la session
        $tauxConversion = session('devise') === 'EUR' ? Devise::whereType('EUR')->value('taux') : '';
        $deviseSymbole = session('devise') === 'EUR' ? '€' : 'CFA';

        // Si une promotion est associée au produit, calculer le prix avec réduction
        if ($this->promotions->isNotEmpty()) {
            $promo = $this->promotions()->first();
            $prix = $this->prix * (1 - $promo->reduction / 100);

            if (session('devise') === 'EUR') {
                // Conversion du prix en devise locale et formatage
                return number_format($prix / $tauxConversion, 2) . ' ' . $deviseSymbole;
            } elseif (session('devise') === 'CFA') {
                // Retour du prix formaté avec le symbole de devise
                return $prix . ' ' . $deviseSymbole;
            }
        } else {
            // Sinon, le prix après réduction est zéro
            return $prix = 0;
        }
    }

    public function getReductionAttribute(): int
    {
        // Vérifie s'il y a des promotions associées et si la première promotion est toujours valide
        if ($this->promotions->isNotEmpty() && now() < $this->promotions()->first()->fin) {
            // Récupère la première promotion active
            $promo = $this->promotions()->first();

            // Retourne la réduction de la promotion
            return $promo->reduction;
        }

        // Si aucune promotion active n'est trouvée ou si la promotion est expirée
        if ($this->promotions->isNotEmpty()) {
            // Met à jour l'état de la promotion expirée
            $this->promotions()->update(['etat' => 'Expiré']);
        }

        // Aucune réduction n'est applicable
        return 0;
    }

    public function getPrixFormatAttribute(): string
    {
        $tauxConversion = 1; // Par défaut, aucun taux de conversion

        if (session('devise') === 'EUR') {
            $devise = Devise::whereType('EUR')->first();
            if ($devise) {
                $tauxConversion = $devise->taux;
            }
            $prixFormat = number_format($this->attributes['prix'] / $tauxConversion, 2);
            $deviseSymbole = '€';
        } elseif (session('devise') === 'CFA') {

            $prixFormat = number_format($this->attributes['prix'], 0, ',', ' ');
            $deviseSymbole = 'CFA';
        }

        return $prixFormat . ' ' . $deviseSymbole;
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
        return Storage::url($this->attributes['cover']);
        // return asset('admin/assets/imgs/theme/logo.svg');
    }

    public function DocLink(): string
    {
        return Storage::url($this->attributes['cover']);
    }

    /**
     * Get all of the images for the Product
     */
    public function images(): HasMany
    {
        return $this->hasMany(Image::class);
    }
}
