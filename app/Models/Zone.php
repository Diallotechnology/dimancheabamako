<?php

namespace App\Models;

use App\Helper\DateFormat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Zone extends Model
{
    use DateFormat;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['nom'];

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = ['pays'];

    /**
     * Get all of the pays for the Zone
     */
    public function pays(): HasMany
    {
        return $this->hasMany(Pays::class);
    }
}
