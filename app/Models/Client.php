<?php

namespace App\Models;

use App\Helper\DateFormat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use DateFormat;

    /**
     * Get all of the orders for the Client
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
