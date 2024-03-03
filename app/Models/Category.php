<?php

namespace App\Models;

use App\Helper\DateFormat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use DateFormat;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['nom', 'description', 'promo'];

    /**
     * Get all of the products for the Category
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
