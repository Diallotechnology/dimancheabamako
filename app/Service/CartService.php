<?php

declare(strict_types=1);

namespace App\Service;

use InvalidArgumentException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Session\SessionManager;

final class CartService
{
    private const SESSION_KEY = 'shopping_cart';
    private const CACHE_TTL = 1440; // 24 heures en minutes

    private SessionManager $session;
    private int $userId;

    public function __construct(SessionManager $session)
    {
        $this->session = $session;
        if (Auth::check()) {
            $this->userId = Auth::user()->id;
        }
    }

    public function setUser(int $userId): self
    {
        $this->userId = $userId ?? null;
        return $this;
    }

    private function getSessionKey(): string
    {
        return $this->userId
            ? self::SESSION_KEY . '_' . $this->userId
            : self::SESSION_KEY;
    }

    /**
     * Ajoute un article au panier.
     * Vérifie que le prix et la quantité sont valides, met à jour la quantité si l'article existe déjà, et enregistre les modifications.
     */
    public function add(int $id, string $name, int $price, int $stock, ?array $attributes = []): Collection
    {
        if ($price < 0) {
            throw new InvalidArgumentException('Le prix ne peut pas être négatif');
        }
        $cartItems = $this->getContent();

        if (!$cartItems->has($id)) {
            $cartItem = collect([
                'id' => $id,
                'name' => $name,
                'price' => $price,
                'quantity' => 1,
                'stock' => $stock,
                'attributes' => collect($attributes),
                'total' => $price,
                'added_at' => now(),
                'user_id' => $this->userId,
            ]);
        }

        $cartItems->put($id, $cartItem);
        $this->saveCart($cartItems);

        return $cartItem;
    }

    /**
     * Met à jour la quantité d'un article existant.
     *
     */
    public function update_cart(int $id, string $type): bool
    {
        $cartItems = $this->getContent();

        if ($cartItems->has($id)) {
            $cartItem = $cartItems->get($id);

            if ($type === 'add') {
                $quantity = $cartItem['quantity'] + 1;
                // si qte superieure au stock
                if ($quantity > $cartItem['stock']) {
                    flash()->warning('Quantité non disponible!');
                    return false;
                }
                $cartItem['quantity'] = $quantity;
            } elseif ($type === 'remove') {
                $quantity = $cartItem['quantity'] - 1;
                if ($quantity < 1) {
                    flash()->warning('Quantité minimun est de 1');
                    return false;
                }
                $cartItem['quantity'] = $quantity;
            }
            $cartItem['total'] = $cartItem['price'] * $quantity;
            $cartItem['updated_at'] = now();
            $cartItems->put($id, $cartItem);
            $this->saveCart($cartItems);
            return true;
        }
        return false;
    }

    /**
     * Supprime un article du panier en fonction de son identifiant.
     */
    public function remove(int $id): bool
    {
        $cartItems = $this->getContent();

        if ($cartItems->has($id)) {
            $cartItems->forget($id);
            $this->saveCart($cartItems);
            return true;
        }

        return false;
    }


    /**
     * Vider le panier.
     */
    public function clear(): void
    {
        $this->session->forget($this->getSessionKey());
        if (method_exists(Cache::getStore(), 'tags')) {
            Cache::tags(['cart'])->forget($this->getCacheKey());
        } else {
            Cache::forget($this->getCacheKey());
        }
    }

    /**
     *  Recupere le contenu du panier.
     */
    public function getContent(): Collection
    {
        return collect($this->session->get($this->getSessionKey(), collect()))->map(function ($item) {
            $item['added_at'] = Carbon::parse($item['added_at']);
            if (isset($item['updated_at'])) {
                $item['updated_at'] = Carbon::parse($item['updated_at']);
            }
            return collect($item);
        });
    }

    /**
     * Calcule le total du panier en additionnant les prix multipliés par les quantités.
     */
    public function getTotal(): int
    {
        return $this->getContent()->sum('total');
    }

    public function getCount(): int
    {
        return $this->getContent()->count();
    }

    public function getTotalQuantity(): int
    {
        return $this->getContent()->sum('quantity');
    }

    public function has(int $id): bool
    {
        return $this->getContent()->has($id);
    }

    public function get(int $id): ?Collection
    {
        return collect($this->getContent()->get($id));
    }

    public function search(callable $callback): Collection
    {
        return $this->getContent()->filter($callback);
    }

    public function sortBy(string $key, bool $descending = false): Collection
    {
        return $descending
            ? $this->getContent()->sortByDesc($key)
            : $this->getContent()->sortBy($key);
    }

    public function groupBy(string $key): Collection
    {
        return $this->getContent()->groupBy($key);
    }

    private function saveCart(Collection $items): void
    {
        $this->session->put($this->getSessionKey(), $items);

        if (method_exists(Cache::getStore(), 'tags')) {
            Cache::tags(['cart'])->put(
                $this->getCacheKey(),
                $items,
                now()->addMinutes(self::CACHE_TTL)
            );
        } else {
            Cache::put(
                $this->getCacheKey(),
                $items,
                now()->addMinutes(self::CACHE_TTL)
            );
        }
    }

    private function getCacheKey(): string
    {
        return $this->userId
            ? "cart_{$this->userId}"
            : "cart_guest";
    }
}
