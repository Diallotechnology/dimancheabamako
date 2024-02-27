<?php

namespace App\Models;

use App\Helper\DateFormat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transport extends Model
{
    use DateFormat;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['nom'];

    /**
     * Get all of the shippings for the Transport
     */
    public function shippings(): HasMany
    {
        return $this->hasMany(Shipping::class);
    }

    /**
     * The zones that belong to the Transport
     */
    public function zones(): BelongsToMany
    {
        return $this->belongsToMany(Zone::class);
    }

    /**
     * Get all of the orders for the Transport
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Transport::class);
    }
}
