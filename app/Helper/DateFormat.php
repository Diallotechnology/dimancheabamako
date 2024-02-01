<?php

declare(strict_types=1);

namespace App\Helper;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

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

    public function category_view(): string
    {
        return $this->categorie ? $this->categorie->nom : '';
    }
}
