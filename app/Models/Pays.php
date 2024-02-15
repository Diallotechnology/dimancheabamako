<?php

namespace App\Models;

use App\Helper\DateFormat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pays extends Model
{
    use DateFormat;

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = ['zone_id', 'nom'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['nom', 'zone_id'];

    /**
     * Get the zone that owns the Pays
     */
    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }
}
