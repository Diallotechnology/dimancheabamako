<?php

declare(strict_types=1);

namespace App\Models;

use App\Helper\DateFormat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

/**
 * @property int $id
 * @property int $transport_id
 * @property int $zone_id
 * @property int $poids_id
 * @property string $temps
 * @property int $montant
 * @property string $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read float $montant_devise
 * @property-read Poids $poids
 * @property-read Transport $transport
 * @property-read Zone $zone
 * @method static \Database\Factories\ShippingFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Shipping newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Shipping newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Shipping query()
 * @method static \Illuminate\Database\Eloquent\Builder|Shipping whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shipping whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shipping whereMontant($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shipping wherePoidsId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shipping whereTemps($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shipping whereTransportId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shipping whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shipping whereZoneId($value)
 * @mixin \Eloquent
 */
final class Shipping extends Model
{
    use DateFormat;

    protected $appends = ['montant_devise'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['zone_id', 'transport_id', 'poids_id', 'montant', 'temps'];

    /**
     * Get the zone that owns the Shipping
     */
    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    /**
     * Get the transport that owns the Shipping
     */
    public function transport(): BelongsTo
    {
        return $this->belongsTo(Transport::class);
    }

    /**
     * Get the poid that owns the Shipping
     */
    public function poids(): BelongsTo
    {
        return $this->belongsTo(Poids::class);
    }

    public function getMontantDeviseAttribute(): float
    {
        $devise = session('devise', 'CFA'); // default
        $montant = (float) $this->montant;

        if ($devise === 'EUR') {
            $taux = Cache::get('taux_eur', 1); // jamais en session

            return round($montant / $taux, 2);
        }

        // CFA
        return $montant;
    }

    // public function getMontantDeviseFormatAttribute(): string
    // {
    //     $montant = $this->montant_devise;
    //     $devise = session('devise', 'CFA');

    //     if ($devise === 'EUR') {
    //         return number_format($montant, 2, ',', ' ') . ' €';
    //     }

    //     return number_format($montant, 0, ',', ' ') . ' CFA';
    // }
}
