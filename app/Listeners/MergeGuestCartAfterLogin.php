<?php

namespace App\Listeners;

use App\Services\CartMergeService;
use Illuminate\Auth\Events\Login;

class MergeGuestCartAfterLogin
{
    public function __construct(private CartMergeService $cartMergeService)
    {
    }

    public function handle(Login $event): void
    {
        $summary = $this->cartMergeService->mergeGuestCartIntoUser((int) $event->user->getAuthIdentifier());

        if (($summary['merged_count'] ?? 0) <= 0) {
            return;
        }

        $message = 'Merged '.$summary['merged_count'].' item(s) from your guest cart.';

        if (($summary['adjusted_count'] ?? 0) > 0) {
            $message .= ' '.$summary['adjusted_count'].' item(s) were adjusted to stock limits.';
        }

        session()->flash('cart_notice', $message);
    }
}
