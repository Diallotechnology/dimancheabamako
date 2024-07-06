<?php

namespace App\Models;

use App\Helper\DateFormat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use DateFormat;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['categorie_id', 'reference', 'nom', 'color', 'taille', 'description', 'resume', 'poids', 'video', 'prix', 'cover', 'stock', 'favoris'];

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
        $tauxConversion = session('locale') === 'fr' ? Devise::whereType('EUR')->value('taux') : Devise::whereType('USD')->value('taux');

        return number_format($this->pivot->montant / $tauxConversion, 2);
    }

    public function getPrixFinalAttribute(): string
    {
        // Récupération du taux de conversion et du symbole de devise en fonction de la locale de la session
        $tauxConversion = session('locale') === 'fr' ? Devise::whereType('EUR')->value('taux') : Devise::whereType('USD')->value('taux');
        // Conversion du prix en devise locale et formatage
        $deviseSymbole = session('locale') === 'fr' ? '€' : '$';
        // Si une promotion est associée au produit, calculer le prix avec réduction
        if ($this->promotions->isNotEmpty()) {
            $promo = $this->promotions()->first();
            $prix = $this->prix * (1 - $promo->reduction / 100);

            // Conversion du prix en devise locale et formatage
            $prixFormat = number_format($prix / $tauxConversion, 2);

            // Retour du prix formaté avec devise
            return $prixFormat.' '.$deviseSymbole;
        } else {
            return number_format($this->prix / $tauxConversion, 2).' '.$deviseSymbole;
        }
    }

    public function getPrixPromoAttribute(): string
    {
        // Récupération du taux de conversion et du symbole de devise en fonction de la locale de la session
        $tauxConversion = session('locale') === 'fr' ? Devise::whereType('EUR')->value('taux') : Devise::whereType('USD')->value('taux');
        $deviseSymbole = session('locale') === 'fr' ? '€' : '$';

        // Si une promotion est associée au produit, calculer le prix avec réduction
        if ($this->promotions->isNotEmpty()) {
            $promo = $this->promotions()->first();
            $prix = $this->prix * (1 - $promo->reduction / 100);

            // Conversion du prix en devise locale et formatage
            $prixFormat = number_format($prix / $tauxConversion, 2);

            // Retour du prix formaté avec le symbole de devise
            return $prixFormat.' '.$deviseSymbole;
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

        if (session('locale') === 'fr') {
            $devise = Devise::whereType('EUR')->first();
            if ($devise) {
                $tauxConversion = $devise->taux;
            }
            $deviseSymbole = '€';
        } elseif (session('locale') === 'en') {
            $devise = Devise::whereType('USD')->first();
            if ($devise) {
                $tauxConversion = $devise->taux;
            }
            $deviseSymbole = '$';
        }

        $prixFormat = number_format($this->attributes['prix'] / $tauxConversion, 2);

        return $prixFormat.' '.$deviseSymbole;
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
