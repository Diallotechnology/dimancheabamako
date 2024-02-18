<?php

namespace App\Models;

use App\Helper\DateFormat;
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

    // public function getCoverAttribute($image)
    // {
    //     return Storage::url($image);
    // }

    public function DocLink(): string
    {
        return Storage::url($this->cover);
    }

    /**
     * Get all of the images for the Product
     */
    public function images(): HasMany
    {
        return $this->hasMany(Image::class);
    }
}
