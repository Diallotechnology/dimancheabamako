<?php

namespace App\Models;

use App\Helper\DateFormat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Image extends Model
{
    use DateFormat;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['product_id', 'chemin', 'extension'];

    /**
     * Get the product that owns the Image
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
