<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\MenuItem;
use Illuminate\Support\Collection;

class CartService
{
    private const SESSION_KEY = 'cart';

    public function add(int $menuItemId, int $qty = 1): void
    {
        $cart = $this->raw();
        $cart[$menuItemId] = ($cart[$menuItemId] ?? 0) + $qty;
        session([self::SESSION_KEY => $cart]);
    }

    public function remove(int $menuItemId): void
    {
        $cart = $this->raw();
        unset($cart[$menuItemId]);
        session([self::SESSION_KEY => $cart]);
    }

    public function update(int $menuItemId, int $qty): void
    {
        if ($qty <= 0) {
            $this->remove($menuItemId);

            return;
        }

        $cart = $this->raw();
        $cart[$menuItemId] = $qty;
        session([self::SESSION_KEY => $cart]);
    }

    public function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    /** @return Collection<int, object{item: MenuItem, quantity: int, subtotal_cents: int}> */
    public function items(): Collection
    {
        $raw = $this->raw();

        if (empty($raw)) {
            return collect();
        }

        $menuItems = MenuItem::whereIn('id', array_keys($raw))->with('category')->get()->keyBy('id');

        return collect($raw)
            ->map(function (int $qty, int $id) use ($menuItems) {
                $item = $menuItems->get($id);

                if (! $item) {
                    return null;
                }

                return (object) [
                    'item' => $item,
                    'quantity' => $qty,
                    'subtotal_cents' => $item->price_cents * $qty,
                ];
            })
            ->filter()
            ->values();
    }

    public function totalCents(): int
    {
        return $this->items()->sum('subtotal_cents');
    }

    public function count(): int
    {
        return array_sum($this->raw());
    }

    public function isEmpty(): bool
    {
        return empty($this->raw());
    }

    /** @return array<int, int> */
    private function raw(): array
    {
        return session(self::SESSION_KEY, []);
    }
}
