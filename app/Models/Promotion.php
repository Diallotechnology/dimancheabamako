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

    public function debutat(): string
    {
        return Carbon::parse($this->attributes['debut'])->format('d/m/Y H:i');
    }

    public function finat(): string
    {
        return Carbon::parse($this->attributes['fin'])->format('d/m/Y H:i');
    }
}
