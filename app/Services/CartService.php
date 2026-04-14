<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class CartService
{
    public function __construct(
        private GuestCartRepository $guestCartRepository,
        private UserCartRepository $userCartRepository
    ) {
    }

    public function getCurrentItemMap(): array
    {
        if ($this->isAuthenticated()) {
            return $this->userCartRepository->getItemMap($this->currentUserId());
        }

        return $this->guestCartRepository->getItemMap();
    }

    public function addItem(int $productId, int $qty = 1): array
    {
        if ($qty <= 0) {
            return ['ok' => false, 'reason' => 'invalid_quantity'];
        }

        $product = $this->resolveProductOrNull($productId);
        if (!$product) {
            return ['ok' => false, 'reason' => 'invalid_product'];
        }

        $currentMap = $this->getCurrentItemMap();
        $currentQty = $currentMap[$productId] ?? 0;
        $requestedQty = $currentQty + $qty;
        $finalQty = $this->clampQuantity($requestedQty, $product->stock);

        if ($finalQty <= 0) {
            return ['ok' => false, 'reason' => 'out_of_stock'];
        }

        if ($this->isAuthenticated()) {
            $this->userCartRepository->setItem($this->currentUserId(), $productId, $finalQty);
        } else {
            $this->guestCartRepository->setItem($productId, $finalQty);
        }

        return [
            'ok' => true,
            'requested_qty' => $requestedQty,
            'final_qty' => $finalQty,
            'adjusted' => $finalQty !== $requestedQty,
        ];
    }

    public function setItem(int $productId, int $qty): array
    {
        $product = $this->resolveProductOrNull($productId);
        if (!$product) {
            return ['ok' => false, 'reason' => 'invalid_product'];
        }

        $finalQty = $this->clampQuantity($qty, $product->stock);

        if ($this->isAuthenticated()) {
            $userId = $this->currentUserId();

            if ($finalQty <= 0) {
                $this->userCartRepository->removeItem($userId, $productId);
            } else {
                $this->userCartRepository->setItem($userId, $productId, $finalQty);
            }
        } else {
            if ($finalQty <= 0) {
                $this->guestCartRepository->removeItem($productId);
            } else {
                $this->guestCartRepository->setItem($productId, $finalQty);
            }
        }

        return [
            'ok' => true,
            'requested_qty' => $qty,
            'final_qty' => $finalQty,
            'adjusted' => $finalQty !== $qty,
        ];
    }

    public function removeItem(int $productId): void
    {
        if ($this->isAuthenticated()) {
            $this->userCartRepository->removeItem($this->currentUserId(), $productId);
            return;
        }

        $this->guestCartRepository->removeItem($productId);
    }

    public function clear(): void
    {
        if ($this->isAuthenticated()) {
            $this->userCartRepository->clear($this->currentUserId());
            return;
        }

        $this->guestCartRepository->clear();
    }

    private function resolveProductOrNull(int $productId): ?Product
    {
        if ($productId <= 0) {
            return null;
        }

        return Product::query()
            ->where('id', $productId)
            ->where('is_active', true)
            ->first();
    }

    private function clampQuantity(int $qty, int $stock): int
    {
        if ($qty <= 0 || $stock <= 0) {
            return 0;
        }

        return min($qty, $stock);
    }

    private function isAuthenticated(): bool
    {
        return Auth::check();
    }

    private function currentUserId(): int
    {
        return (int) Auth::id();
    }
}