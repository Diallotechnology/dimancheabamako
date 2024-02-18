<?php

namespace App\Models;

use App\Helper\DateFormat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ville extends Model
{
    use DateFormat;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['nom', 'country_id'];

    /**
     * Get the countrie that owns the Ville
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
}
