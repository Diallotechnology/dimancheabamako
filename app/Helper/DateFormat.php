<?php

declare(strict_types=1);

namespace App\Helper;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

trait DateFormat
{
    use HasFactory;

    protected function getCreatedAtAttribute(string $date): string
    {
        return Carbon::parse($date)->format('d/m/Y');
    }

    // public function getDelaiFormatAttribute(): string
    // {
    //     return Carbon::parse($this->delai)->format('d/m/Y');
    // }

    // public function category_view(): string
    // {
    //     return $this->categorie ? $this->categorie->nom : '';
    // }

    // public function getPrixAttribute($prix)
    // {
    //     return number_format($prix, 0, ',', ' ').' CFA';
    // }

    public function DocLink(): string
    {
        return Storage::url($this->chemin);
    }
    // public function getPrixAttribute(int $prix): string
    // {
    //     return number_format($prix, 0, ',', ' ').' CFA';
    // }

}
