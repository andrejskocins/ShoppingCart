<x-app-layout>
	<x-slot name="header">
		<div class="flex items-center justify-between gap-4">
			<div>
				<h2 class="font-semibold text-xl text-gray-800 leading-tight">Cart</h2>
				<p class="text-sm text-gray-500 mt-1">Review, update quantities, or remove items.</p>
			</div>
			<a href="{{ route('home') }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
				Continue Shopping
			</a>
		</div>
	</x-slot>

	<div class="py-8">
		<div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
			@if (session('cart_notice'))
				<div class="mb-6 rounded-md border border-green-300 bg-green-50 px-4 py-3 text-sm text-green-900">
					{{ session('cart_notice') }}
				</div>
			@endif

			@if (count($lines) === 0)
				<div class="rounded-xl border border-gray-200 bg-white p-8 text-center">
					<p class="text-lg font-medium text-gray-900">Your cart is empty.</p>
					<p class="mt-2 text-sm text-gray-500">Add products from the shop to see them here.</p>
				</div>
			@else
				<div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
					@foreach ($lines as $line)
						<article class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
							<div class="flex items-start justify-between gap-3">
								<div>
									<h3 class="text-base font-semibold text-gray-900">{{ $line['product']->name }}</h3>
									<p class="text-sm text-gray-500 mt-1">Unit price: ${{ number_format($line['product']->price_cents / 100, 2) }}</p>
								</div>
								<strong class="text-lg text-gray-900">${{ number_format($line['line_total_cents'] / 100, 2) }}</strong>
							</div>

							<div class="mt-4 flex flex-wrap items-center gap-2">
								<form method="POST" action="{{ route('cart.update', $line['product']->id) }}" class="flex items-center gap-2">
									@csrf
									@method('PATCH')
									<label for="line-qty-{{ $line['product']->id }}" class="text-sm text-gray-700">Quantity:</label>
									<input
										id="line-qty-{{ $line['product']->id }}"
										type="number"
										name="qty"
										min="1"
										max="{{ $line['max_qty'] }}"
										value="{{ $line['qty'] }}"
										class="w-24 rounded-md border-gray-300 text-sm"
									>
									<button type="submit" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
										Update
									</button>
								</form>

								<form method="POST" action="{{ route('cart.remove', $line['product']->id) }}">
									@csrf
									@method('DELETE')
									<button type="submit" class="inline-flex items-center rounded-md border border-red-600 bg-red-600 px-3 py-2 text-sm font-medium text-white hover:bg-red-700">
										Remove
									</button>
								</form>
							</div>
						</article>
					@endforeach
				</div>

				<div class="mt-6 rounded-xl border border-gray-200 bg-white p-4">
					<div class="flex items-center justify-between">
						<span class="text-gray-700">Subtotal</span>
						<strong class="text-lg text-gray-900">${{ number_format($subtotalCents / 100, 2) }}</strong>
					</div>
				</div>
			@endif
		</div>
	</div>
</x-app-layout>
