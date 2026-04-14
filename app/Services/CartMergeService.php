<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\DB;

class CartMergeService
{
    public function __construct(
        private GuestCartRepository $guestCartRepository,
        private UserCartRepository $userCartRepository
    ) {
    }

    public function mergeGuestCartIntoUser(int $userId): array
    {
        $guestMap = $this->guestCartRepository->getItemMap();

        $summary = [
            'merged_count' => 0,
            'adjusted_count' => 0,
            'skipped_count' => 0,
            'guest_item_count' => count($guestMap),
            'final_guest_cleared' => false,
        ];

        if ($guestMap === []) {
            $summary['final_guest_cleared'] = true;
            return $summary;
        }

        DB::transaction(function () use ($userId, $guestMap, &$summary) {
            $dbMap = $this->userCartRepository->getItemMap($userId);

            foreach ($guestMap as $productId => $guestQty) {
                $productId = (int) $productId;
                $guestQty = (int) $guestQty;

                if ($productId <= 0 || $guestQty <= 0) {
                    $summary['skipped_count']++;
                    continue;
                }

                $product = Product::query()
                    ->where('id', $productId)
                    ->where('is_active', true)
                    ->first();

                if (!$product) {
                    $summary['skipped_count']++;
                    continue;
                }

                $existingQty = (int) ($dbMap[$productId] ?? 0);
                $requestedQty = $existingQty + $guestQty;
                $finalQty = min($requestedQty, (int) $product->stock);

                if ($finalQty <= 0) {
                    $summary['skipped_count']++;
                    continue;
                }

                if ($finalQty !== $requestedQty) {
                    $summary['adjusted_count']++;
                }

                $this->userCartRepository->setItem($userId, $productId, $finalQty);
                $summary['merged_count']++;
            }
        });

        $this->guestCartRepository->clear();
        $summary['final_guest_cleared'] = true;

        return $summary;
    }
}