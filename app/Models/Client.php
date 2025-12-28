<?php

declare(strict_types=1);

namespace App\Models;

use LogicException;
use App\Helper\DateFormat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $prenom
 * @property string $nom
 * @property string $contact
 * @property string $pays
 * @property string $email
 * @property string $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Order> $orders
 * @property-read int|null $orders_count
 * @method static \Database\Factories\ClientFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Client newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Client newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Client query()
 * @method static \Illuminate\Database\Eloquent\Builder|Client whereContact($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Client whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Client whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Client whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Client whereNom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Client wherePays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Client wherePrenom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Client whereUpdatedAt($value)
 * @mixin \Eloquent
 */
final class Client extends Model
{
    use DateFormat;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['nom', 'prenom', 'email', 'contact', 'pays'];

    /**
     * Get all of the orders for the Client
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the latest order for the Client.
     *
     * @return Order|null
     */
    public function latestorder()
    {
        return $this->hasOne(Order::class)->latestOfMany()->first();
    }

    protected static function booted()
    {
        static::deleting(function ($client) {
            if ($client->orders()->where('trans_state', 'PURCHASED')->exists()) {
                throw new \LogicException('Cannot delete client with paid orders');
            }
        });
    }
}
