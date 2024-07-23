<?php

namespace App\Models;

use App\Helper\DateFormat;
use Illuminate\Database\Eloquent\Model;

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
class Devise extends Model
{
    use DateFormat;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['type', 'taux'];
}
