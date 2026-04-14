<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;

class UserCartRepository
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function getOrCreateCart(int $userId): Cart
    {
        return Cart::firstOrCreate(
            ['user_id' => $userId],
            ['user_id' => $userId]
        );

    }

    public function getItemMap(int $userId): array
    {
        $cart = $this->getOrCreateCart($userId);

        return CartItem::query()
            ->where('user_id', $cart->id)
            ->pluck('quantity', 'product_id')
            ->mapWithKeys(function ($qty, $productId) {
                return [(int) $productId => (int) $qty];
            })
            ->toArray();
    }

    public function addItem(int $userId, int $productId, int $qty): void
    {
        if ($productId <= 0 || $qty <= 0) {
            return;
        }

        $cart = $this->getOrCreateCart($userId);

        $item = CartItem::query()
            ->where('cart_id', $cart->id)
            ->where('product_id', $productId)
            ->first();

        if ($item) {
            $item->quantity += $qty;
            $item->save();
            return;
        }

        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $productId,
            'quantity' => $qty,
        ]);
    }

    public function setItem(int $userId, int $productId, int $qty): void
    {
        if ($productId <= 0) {
            return;
        }

        if ($qty <= 0) {
            $this->removeItem($userId, $productId);
            return;
        }

        $cart = $this->getOrCreateCart($userId);

        CartItem::updateOrCreate(
            [
                'cart_id' => $cart->id,
                'product_id' => $productId,
            ],
            [
                'quantity' => $qty,
            ]
        );
    }

    public function removeItem(int $userId, int $productId): void
    {
        if ($productId <= 0) {
            return;
        }

        $cart = $this->getOrCreateCart($userId);

        CartItem::query()
            ->where('cart_id', $cart->id)
            ->where('product_id', $productId)
            ->delete();
    }

    public function clear(int $userId): void
    {
        $cart = $this->getOrCreateCart($userId);

        CartItem::query()
            ->where('cart_id', $cart->id)
            ->delete();
    }
}
