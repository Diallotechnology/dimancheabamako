<?php

namespace App\Models;

use App\Helper\DateFormat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transport extends Model
{
    use DateFormat;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['pays_id', 'zone_id', 'nom', 'montant', 'temps', 'poids'];

    /**
     * Get the pays that owns the Transport
     */
    public function pays(): BelongsTo
    {
        return $this->belongsTo(Pays::class);
    }

    /**
     * Get the zone that owns the Transport
     */
    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }
}
