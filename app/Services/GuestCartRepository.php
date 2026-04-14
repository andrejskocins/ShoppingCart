<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class GuestCartRepository
{
    private const COOKIE_NAME = 'guest_cart_v1';
    private const COOKIE_MINUTES = 60 * 24 * 30; // 30 days

    public function __construct(private Request $request)
    {
        //
    }

    public function getItemMap() : array
    {
        $raw = $this->request->cookie(self::COOKIE_NAME);

        if (!is_string($raw) || $raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);

        if (!is_array($decoded)) {
            return [];
        }

        return $this->normalizeMap($decoded);
    }

    private function normalizeMap(array $raw): array
    {
        $normalized = [];

        foreach ($raw as $productId => $qty) {
            $productId = (int) $productId;
            $qty = (int) $qty;

            if ($productId <= 0 || $qty <= 0) {
                continue;
            }

            $normalized[$productId] = $qty;
        }

        return $normalized;
    }

    // Increments quantity
    public function addItem(int $productId, int $qty) : void
    {
        if ($productId <= 0|| $qty <= 0) {
            return;
        }

        $map = $this->getItemMap();
        $existing = $map[$productId] ?? 0;
        $map[$productId] = $existing + $qty;

        $this->persistMap($map);
    }

    // Sets quantity
    public function setItem(int $productId, int $qty): void
    {
        if ($productId <= 0) {
            return;
        }

        $map = $this->getItemMap();

        if ($qty <= 0) {
            unset($map[$productId]);
        } else {
            $map[$productId] = $qty;
        }

        $this->persistMap($map);
    }

    // Decrements quantity
    public function removeItem(int $productId): void
    {
        if ($productId <= 0) {
            return;
        }

        $map = $this->getItemMap();
        unset($map[$productId]);

        $this->persistMap($map);
    }
    
    private function persistMap(array $map): void
    {
        $normalized = $this->normalizeMap($map);

        if ($normalized === []) {
            Cookie::queue(Cookie::forget(self::COOKIE_NAME));
            return;
        }

        Cookie::queue(
            self::COOKIE_NAME,
            json_encode($normalized),
            self::COOKIE_MINUTES
        );
    }

    // Clears item from cart
    public function clear(): void
    {
        Cookie::queue(Cookie::forget(self::COOKIE_NAME));
    }
}
