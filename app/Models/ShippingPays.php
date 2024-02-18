<?php

namespace App\Models;

use App\Helper\DateFormat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShippingPays extends Model
{
    use DateFormat;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['nom', 'shipping_zone_id'];

    /**
     * Get the shippingzone that owns the ShippingPays
     */
    public function ShippingZone(): BelongsTo
    {
        return $this->belongsTo(ShippingZone::class);
    }
}
