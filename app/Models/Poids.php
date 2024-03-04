<?php

namespace App\Models;

use App\Helper\DateFormat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Poids extends Model
{
    use DateFormat;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['min', 'max'];

    /**
     * Get all of the shippings for the Poids
     */
    public function shippings(): HasMany
    {
        return $this->hasMany(Shipping::class);
    }
}
