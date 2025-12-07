<?php

declare(strict_types=1);

namespace App\Service;

use App\Models\Product;
use Illuminate\Session\SessionManager;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;

final class CartService
{
    private const CACHE_TTL = 1440; // minutes (24h)

    private ?int $userId = null;

    private SessionManager $session;

    public function __construct(SessionManager $session)
    {
        $this->session = $session;
        $this->userId = Auth::id();
    }

    public function setUser(int $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    public function clear(): void
    {
        Cache::forget($this->getCacheKey());
    }

    public function mergeGuestCartToUser(int $userId, string $guestSessionId): void
    {
        $guestKey = 'cart_guest_'.$guestSessionId;
        $userKey = "cart_user_{$userId}";

        $guestCart = Cache::get($guestKey, []);
        $userCart = Cache::get($userKey, []);

        if (empty($guestCart)) {
            return;
        }

        foreach ($guestCart as $productId => $itemGuest) {

            // Produit désactivé → on ignore
            if (! Product::where('id', $productId)->where('status', true)->exists()) {
                continue;
            }

            // Fusion
            if (isset($userCart[$productId])) {
                $userCart[$productId]['quantity'] += $itemGuest['quantity'];
                $userCart[$productId]['total'] =
                    $userCart[$productId]['quantity'] * $userCart[$productId]['price'];
            } else {
                $userCart[$productId] = $itemGuest;
            }
        }

        Cache::put($userKey, $userCart, now()->addMinutes(self::CACHE_TTL));
        Cache::forget($guestKey);
    }

    /**
     * Ajoute un produit au panier.
     * Si le produit est déjà présent, on ne modifie pas la quantité.
     */
    public function add(int $id, string $name, int $price, int $stock, int|float $poids, bool $is_preorder = false, ?array $attributes = []): Collection
    {
        if ($price < 0) {
            throw new InvalidArgumentException('Le prix ne peut pas être négatif');
        }

        $items = $this->load();

        // Si le produit existe déjà → on ne touche pas à la quantité, on renvoie l’item existant
        if ($items->has($id)) {
            return $items->get($id);
        }

        // Nouveau produit dans le panier
        $cartItem = collect([
            'id' => $id,
            'name' => $name,
            'price' => $price,
            'quantity' => 1,
            'stock' => $stock,
            'poids' => $poids,
            'is_preorder' => $is_preorder,
            'attributes' => collect($attributes),
            'total' => $price,
            'added_at' => now(),
            'user_id' => $this->userId,
        ]);

        $items->put($id, $cartItem);
        $this->save($items);

        return $cartItem;
    }

    /**
     * Met à jour la quantité (add/remove) d’un produit déjà présent.
     */
    public function update(int $id, int $newQuantity): bool
    {
        $items = $this->load();

        if (! $items->has($id)) {
            return false;
        }

        $cartItem = $items->get($id);

        // quantité minimum
        if ($newQuantity < 1) {
            return false;
        }

        // quantité maximum par rapport au stock
        if ($cartItem['is_preorder'] === \false && $newQuantity > $cartItem['stock']) {
            return false;
        }

        $cartItem['quantity'] = $newQuantity;
        $cartItem['total'] = $newQuantity * $cartItem['price'];
        $cartItem['updated_at'] = now();

        $items->put($id, $cartItem);
        $this->save($items);

        return true;
    }

    public function remove(int $id): bool
    {
        $items = $this->load();
        if (! $items->has($id)) {
            return false;
        }

        $items->forget($id);
        $this->save($items);

        return true;
    }

    public function getContent(): Collection
    {
        return $this->load();
    }

    public function getTotal(): int|float
    {
        return $this->load()->sum('total');
    }

    public function getCount(): int
    {
        return $this->load()->count();
    }

    public function getTotalQuantity(): int
    {
        return $this->load()->sum('quantity');
    }

    public function has(int $id): bool
    {
        return $this->load()->has($id);
    }

    public function get(int $id): ?Collection
    {
        return $this->load()->get($id);
    }

    /**
     * Recherche d’items avec callback.
     */
    private function getCacheKey(): string
    {
        return $this->userId
            ? "cart_user_{$this->userId}"
            : 'cart_guest_'.$this->session->getId();
    }

    private function load(): Collection
    {
        return collect(Cache::get($this->getCacheKey(), []))
            ->map(fn ($item) => collect($item));
    }

    private function save(Collection $items): void
    {
        Cache::put($this->getCacheKey(), $items, now()->addMinutes(self::CACHE_TTL));
    }
}
