<?php

namespace App\Models;

use App\Helper\DateFormat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShippingZone extends Model
{
    use DateFormat;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['nom'];

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = ['ShippingPays'];

    /**
     * Get all of the pays for the ShippingZone
     */
    public function ShippingPays(): HasMany
    {
        return $this->hasMany(ShippingPays::class);
    }
}
