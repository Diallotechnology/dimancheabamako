<?php

namespace App\Models;

use App\Helper\DateFormat;
use Illuminate\Database\Eloquent\Model;

class PayLink extends Model
{
    use DateFormat;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'contact', 'lien', 'montant', 'trans_ref', 'trans_state', 'etat'];
}
