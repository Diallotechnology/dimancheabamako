<?php

declare(strict_types=1);

namespace App\Models;

use App\Helper\DateFormat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * @property int $id
 * @property string $type
 * @property int $taux
 * @property string $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Database\Factories\DeviseFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Devise newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Devise newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Devise query()
 * @method static \Illuminate\Database\Eloquent\Builder|Devise whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Devise whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Devise whereTaux($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Devise whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Devise whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
final class Devise extends Model
{
    use DateFormat;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['type', 'taux'];

    protected static function booted()
    {
        self::updated(function ($devise) {
            if ($devise->type === 'EUR') {
                Cache::forget('taux_eur');
            }
            session()->forget('taux_eur');
        });

        self::created(function ($devise) {
            if ($devise->type === 'EUR') {
                Cache::forget('taux_eur');
            }
            session()->forget('taux_eur');
        });

        self::deleted(function ($devise) {
            if ($devise->type === 'EUR') {
                Cache::forget('taux_eur');
            }
            session()->forget('taux_eur');
        });
    }
}
