<?php

declare(strict_types=1);

namespace App\Models;

use App\Helper\DateFormat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property int $product_id
 * @property string $chemin
 * @property string $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Product $product
 *
 * @method static \Database\Factories\ImageFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Image newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Image newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Image query()
 * @method static \Illuminate\Database\Eloquent\Builder|Image whereChemin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Image whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Image whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Image whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Image whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
final class Image extends Model
{
    use DateFormat;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['product_id', 'chemin'];

    /**
     * Get the product that owns the Image
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getCheminAttribute(): string
    {
        return Storage::url($this->attributes['chemin']);
    }

    public function DocLink(): string
    {
        return Storage::url($this->attributes['chemin']);
    }
}
