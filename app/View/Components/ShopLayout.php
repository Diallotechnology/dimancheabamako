<?php

declare(strict_types=1);

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Layout de la vitrine refondue (Tailwind). Remplace progressivement AppLayout (thème Evara).
 */
final class ShopLayout extends Component
{
    public function render(): View
    {
        return view('layouts.shop');
    }
}
