<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(private CartService $cartService)
    {
    }

    public function index()
    {
        $itemMap = $this->cartService->getCurrentItemMap();
        $productIds = array_map('intval', array_keys($itemMap));

        $products = Product::query()
            ->whereIn('id', $productIds)
            ->where('is_active', true)
            ->get()
            ->keyBy('id');

        $lines = [];
        $subtotalCents = 0;

        foreach ($itemMap as $productId => $qty) {
            $productId = (int) $productId;
            $qty = (int) $qty;
            $product = $products->get($productId);

            if (!$product) {
                continue;
            }

            $maxQty = max(1, (int) $product->stock);
            $safeQty = min($qty, $maxQty);
            $lineTotal = $safeQty * (int) $product->price_cents;
            $subtotalCents += $lineTotal;

            $lines[] = [
                'product' => $product,
                'qty' => $safeQty,
                'max_qty' => $maxQty,
                'line_total_cents' => $lineTotal,
            ];
        }

        return view('cart.index', [
            'lines' => $lines,
            'subtotalCents' => $subtotalCents,
        ]);
    }

    public function add(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'qty' => ['required', 'integer', 'min:1', 'max:10'],
        ]);

        $result = $this->cartService->addItem((int) $validated['product_id'], (int) $validated['qty']);

        if (!$result['ok']) {
            return back()->with('cart_notice', 'Could not add that item to cart.');
        }

        $message = 'Item added to cart.';
        if (!empty($result['adjusted'])) {
            $message = 'Item added, quantity was adjusted to available limit.';
        }

        return back()->with('cart_notice', $message);
    }

    public function update(Request $request, int $productId): RedirectResponse
    {
        $validated = $request->validate([
            'qty' => ['required', 'integer', 'min:1'],
        ]);

        $result = $this->cartService->setItem($productId, (int) $validated['qty']);

        if (!$result['ok']) {
            return back()->with('cart_notice', 'Could not update that cart item.');
        }

        $message = 'Cart quantity updated.';
        if (!empty($result['adjusted'])) {
            $message = 'Quantity adjusted to available limit.';
        }

        return back()->with('cart_notice', $message);
    }

    public function remove(int $productId): RedirectResponse
    {
        $this->cartService->removeItem($productId);

        return back()->with('cart_notice', 'Item removed from cart.');
    }
}
