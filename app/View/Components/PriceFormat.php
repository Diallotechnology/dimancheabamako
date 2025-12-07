<?php

declare(strict_types=1);

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

final class PriceFormat extends Component
{
    public function __construct(
        public int $value
    ) {}

    public function format($v)
    {
        $taux = session('taux_eur', 1);

        return session('devise') === 'EUR'
            ? number_format($v / $taux, 2, ',', ' ').' €'
            : number_format($v, 0, ',', ' ').' FCFA';
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.price-format');
    }
}
