<?php

namespace App\Models;

use App\Helper\DateFormat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

class Promotion extends Model
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

    // protected function getDebutAttribute(string $date): string
    // {
    //     return Carbon::parse($date)->format('d/m/Y H:i');
    // }

    // protected function getFinAttribute(string $date): string
    // {
    //     return Carbon::parse($date)->format('d/m/Y H:i');
    // }
}
