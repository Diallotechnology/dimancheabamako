<?php

namespace App\Models;

use App\Helper\DateFormat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property float $min
 * @property float $max
 * @property string $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Shipping> $shippings
 * @property-read int|null $shippings_count
 *
 * @method static \Database\Factories\PoidsFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Poids newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Poids newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Poids query()
 * @method static \Illuminate\Database\Eloquent\Builder|Poids whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Poids whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Poids whereMax($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Poids whereMin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Poids whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
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
