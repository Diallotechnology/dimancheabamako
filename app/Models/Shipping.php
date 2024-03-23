<?php

namespace App\Models;

use App\Helper\DateFormat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shipping extends Model
{
    use DateFormat;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['zone_id', 'transport_id', 'poids_id', 'montant', 'temps'];

    /**
     * Get the zone that owns the Shipping
     */
    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    /**
     * Get the transport that owns the Shipping
     */
    public function transport(): BelongsTo
    {
        return $this->belongsTo(Transport::class);
    }

    /**
     * Get the poid that owns the Shipping
     */
    public function poids(): BelongsTo
    {
        return $this->belongsTo(Poids::class);
    }

    public function getPrixFinalAttribute(): string
    {
        $deviseSymbole = session('locale') === 'fr' ? '€' : '$';
        $tauxConversion = session('locale') === 'fr' ? Devise::whereType('EUR')->value('taux') : Devise::whereType('USD')->value('taux');
        // Conversion du prix en devise locale et formatage
        $prixFormat = number_format($this->montant / $tauxConversion, 2);

        return $prixFormat.' '.$deviseSymbole;
    }
}
