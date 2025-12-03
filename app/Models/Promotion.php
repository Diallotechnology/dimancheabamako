<?php

declare(strict_types=1);

namespace App\Models;

use App\Helper\DateFormat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $nom
 * @property string $etat
 * @property int $reduction
 * @property string $debut
 * @property string $fin
 * @property string $created_at
 * @property Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Product> $products
 * @property-read int|null $products_count
 *
 * @method static \Database\Factories\PromotionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Promotion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Promotion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Promotion query()
 * @method static \Illuminate\Database\Eloquent\Builder|Promotion whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Promotion whereDebut($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Promotion whereEtat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Promotion whereFin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Promotion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Promotion whereNom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Promotion whereReduction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Promotion whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
final class Promotion extends Model
{
    use DateFormat;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['reduction', 'nom', 'debut', 'fin', 'etat'];

    /**
     * The products that belong to the Promotion
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }

    public function scopeActive(Builder $query)
    {
        return $query
            ->where('etat', 'En cours')
            ->where('debut', '<=', now())
            ->where('fin', '>=', now());
    }

    public function debutat(): string
    {
        return Carbon::parse($this->attributes['debut'])->format('d/m/Y H:i');
    }

    public function finat(): string
    {
        return Carbon::parse($this->attributes['fin'])->format('d/m/Y H:i');
    }
}
