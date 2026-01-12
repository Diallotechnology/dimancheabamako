<?php

declare(strict_types=1);

namespace App\Models;

use App\Helper\DateFormat;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string|null $trans_ref
 * @property string|null $trans_state
 * @property string $name
 * @property string $contact
 * @property string $lien
 * @property int $montant
 * @property string $etat
 * @property string $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayLink newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayLink newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayLink query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayLink whereContact($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayLink whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayLink whereEtat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayLink whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayLink whereLien($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayLink whereMontant($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayLink whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayLink whereTransRef($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayLink whereTransState($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayLink whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
final class PayLink extends Model
{
    use DateFormat;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'contact', 'lien', 'montant', 'trans_ref', 'trans_state', 'etat'];
}
