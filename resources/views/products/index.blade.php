<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Shop') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">Browse products and add what you like to your cart.</p>
            </div>
            @if (Route::has('cart.index'))
                <a href="{{ route('cart.index') }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    View Cart
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('cart_notice'))
                <div class="mb-6 rounded-md border border-green-300 bg-green-50 px-4 py-3 text-sm text-green-900">
                    {{ session('cart_notice') }}
                </div>
            @endif

            @if ($products->count() === 0)
                <div class="rounded-xl border border-gray-200 bg-white p-8 text-center">
                    <p class="text-lg font-medium text-gray-900">No products available right now.</p>
                    <p class="mt-2 text-sm text-gray-500">Seed products or create new items to populate this page.</p>
                </div>
            @else
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach ($products as $product)
                        <article class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                            <div class="aspect-[4/3] w-full rounded-md bg-gray-100 mb-4"></div>

                            <h3 class="text-base font-semibold text-gray-900">{{ $product->name }}</h3>
                            <p class="mt-1 text-sm text-gray-500">{{ $product->description ?: 'No description yet.' }}</p>

                            <div class="mt-4 flex items-center justify-between text-sm">
                                <span class="font-semibold text-gray-900">${{ number_format($product->price_cents / 100, 2) }}</span>
                                @if ($product->stock < 10)
                                    <span class="text-gray-500">Only {{ $product->stock }} in stock!</span>
                                @endif
                            </div>

                            @if (Route::has('cart.add'))
                                <form method="POST" action="{{ route('cart.add') }}" class="mt-4 flex items-center gap-2">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <input
                                        type="number"
                                        name="qty"
                                        min="1"
                                        max="{{ $product->stock }}"
                                        value="1"
                                        class="w-20 rounded-md border-gray-300 text-sm"
                                    >
                                    <button
                                        type="submit"
                                        class="inline-flex items-center rounded-md border border-indigo-600 bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                                    >
                                        Add
                                    </button>
                                </form>
                            @else
                                <div class="mt-4 rounded-md border border-dashed border-gray-300 px-3 py-2 text-xs text-gray-500">
                                    Cart actions will appear here after cart routes are added.
                                </div>
                            @endif
                        </article>
                    @endforeach
                </div>

                <div class="mt-8">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
