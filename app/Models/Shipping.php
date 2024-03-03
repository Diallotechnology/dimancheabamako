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
    protected $fillable = ['zone_id', 'transport_id', 'montant', 'temps', 'poids'];

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
}
