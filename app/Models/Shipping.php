<?php

namespace App\Models;

use App\Helper\DateFormat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shipping extends Model
{
    use DateFormat;

    protected $appends = ['montant_devise'];

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

    public function getMontantDeviseAttribute(): float
    {
        if (session('devise') === 'EUR') {
            $tauxConversion = session('devise') === 'EUR' ? Devise::whereType('EUR')->value('taux') : '';

            // Conversion du prix en devise locale et formatage
            return number_format($this->montant / $tauxConversion, 2);

        } elseif (session('devise') === 'CFA') {

            return $this->montant;
        }
    }
}
