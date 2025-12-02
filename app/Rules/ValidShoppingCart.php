<?php

declare(strict_types=1);

namespace App\Rules;

use App\Models\Product;
use App\Service\CartService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final readonly class ValidShoppingCart implements ValidationRule
{
    public function __construct(
        private CartService $cartService
    ) {}

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $cart = $this->cartService->getContent();

        if ($cart->isEmpty()) {
            $fail('Votre panier est vide.');

            return;
        }

        foreach ($cart as $item) {
            $product = Product::find($item['id']);

            if (! $product) {
                $fail("Le produit « {$item['name']} » n'existe plus.");

                return;
            }

            if ($product->status === false) {
                $fail("Le produit « {$item['name']} » est désactivé et ne peut pas être acheté.");

                return;
            }

            if ($product->stock < $item['quantity']) {
                $fail("La quantité demandée pour « {$item['name']} » dépasse le stock disponible.");

                return;
            }
        }
    }
}
