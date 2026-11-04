<?php

declare(strict_types=1);

namespace App\Models;

use App\Helper\DateFormat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $nom
 * @property string $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Country> $countries
 * @property-read int|null $countries_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Shipping> $shippings
 * @property-read int|null $shippings_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Transport> $transports
 * @property-read int|null $transports_count
 *
 * @method static \Database\Factories\ZoneFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Zone newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Zone newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Zone query()
 * @method static \Illuminate\Database\Eloquent\Builder|Zone whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Zone whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Zone whereNom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Zone whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
final class Zone extends Model
{
    use DateFormat;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['nom'];

    /**
     * Get all of the countries for the Zone
     */
    public function countries(): HasMany
    {
        return $this->hasMany(Country::class);
    }

    /**
     * Get all of the shippings for the Zone
     */
    public function shippings(): HasMany
    {
        return $this->hasMany(Shipping::class);
    }

    /**
     * The transport that belong to the Zone
     */
    public function transports(): BelongsToMany
    {
        return $this->belongsToMany(Transport::class);
    }
}
