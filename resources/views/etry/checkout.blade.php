<x-layout title="Checkout">
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">🛒 Checkout</h1>

        <form action="{{ route('checkout.process') }}" method="POST">
            @csrf

            @if(isset($product))
                {{-- Hidden flag for single item checkout --}}
                <input type="hidden" name="type" value="single">
                <input type="hidden" name="product_id" value="{{ $product->id }}">

                <div class="bg-white rounded-lg shadow-lg p-4 flex items-center mb-4">
                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-24 h-24 object-cover rounded">
                    <div class="ml-4 flex-grow">
                        <h2 class="text-lg font-bold">{{ $product->name }}</h2>
                        <p class="text-gray-600">{{ $product->description }}</p>
                        <p class="text-lg font-semibold">₱{{ $product->price }}</p>

                        {{-- Size Selection --}}
                        <div class="mt-2">
                            <label class="font-semibold">Select Size:</label><br>
                            @foreach ($product->stocks->where('stock', '>', 0) as $stock)
                                <label class="inline-flex items-center mr-4 mt-1">
                                    <input type="radio" name="size" value="{{ $stock->size }}" required class="mr-1">
                                    {{ strtoupper($stock->size) }} ({{ $stock->stock }} available)
                                </label>
                            @endforeach
                        </div>

                        <div class="mt-2">
                            <label for="quantity" class="font-semibold">Quantity:</label>
                            <input type="number" name="quantity" id="quantity" value="1" min="1" class="border px-2 py-1 rounded w-20 ml-2">
                        </div>
                    </div>
                </div>

            @elseif(isset($cartItems) && count($cartItems) > 0)
                {{-- Hidden flag for cart checkout --}}
                <input type="hidden" name="type" value="cart">

                @foreach($cartItems as $item)
                    <div class="bg-white rounded-lg shadow-lg p-4 flex items-center mb-4">
                        <img src="{{ asset('storage/' . $item->product->image) }}" 
                             alt="{{ $item->product->name }}" 
                             class="w-24 h-24 object-cover rounded">
                        <div class="ml-4 flex-grow">
                            <h2 class="text-lg font-bold">{{ $item->product->name }}</h2>
                            <p class="text-gray-600">{{ $item->product->description }}</p>
                            <p class="text-lg font-semibold">₱{{ $item->product->price }}</p>

                            <input type="hidden" name="items[{{ $item->id }}][product_id]" value="{{ $item->product->id }}">

                            {{-- Size Selection --}}
                            <label class="block mt-2 font-semibold">Select Size:</label>
                            @foreach ($item->product->stocks->where('stock', '>', 0) as $stock)
                                <label class="inline-flex items-center mr-4 mt-1">
                                    <input type="radio" name="items[{{ $item->id }}][size]" value="{{ $stock->size }}" required class="mr-1">
                                    {{ strtoupper($stock->size) }} ({{ $stock->stock }} left)
                                </label>
                            @endforeach

                            <label class="block mt-2 font-semibold">Quantity:</label>
                            <input type="number" name="items[{{ $item->id }}][quantity]" id="quantity_{{ $item->id }}" value="1" min="1" class="border rounded px-2 py-1 w-20">
                        </div>
                    </div>
                @endforeach
            @else
                <p class="text-gray-500">No items selected for checkout.</p>
            @endif

             {{-- Address Section --}}
            <div class="container w-1/2 bg-white rounded-lg shadow-lg p-4 mb-4">
                <h2 class="text-lg font-bold mb-2">Shipping Address</h2>
                <div class="grid grid-cols-1 md:grid-cols-1 gap-4">
                    <div>
                        <label for="address" class="font-semibold block">Street:</label>
                        <input type="text" name="address" id="address" value="{{ old('address', auth()->user()->address->address ?? '') }}" required class="border px-2 py-1 rounded w-full">
                    </div>
                    <div>
                        <label for="city" class="font-semibold block">City:</label>
                        <input type="text" name="city" id="city" value="{{ old('city', auth()->user()->address->city ?? '') }}" required class="border px-2 py-1 rounded w-full">
                    </div>
                    <div>
                        <label for="state" class="font-semibold block">State/Province:</label>
                        <input type="text" name="state" id="state" value="{{ old('state', auth()->user()->address->state ?? '') }}" required class="border px-2 py-1 rounded w-full">
                    </div>
                    <div>
                        <label for="zip" class="font-semibold block">Zip/Postal Code:</label>
                        <input type="text" name="zip" id="zip" value="{{ old('zip', auth()->user()->address->zip ?? '') }}" required class="border px-2 py-1 rounded w-full">
                    </div>
                </div>
            </div>

            @if(isset($product) || (isset($cartItems) && count($cartItems) > 0))
                <div class="mt-4 text-right">
                    <p class="text-xl font-bold mb-2">Total: ₱<span id="total-price">0.00</span></p>
                    <button type="submit" class="bg-green-500 text-white px-6 py-2 rounded-lg hover:bg-green-600">
                        Buy Now
                    </button>
                </div>
            @endif
        </form>
    </div>

    {{-- JavaScript for Dynamic Total --}}
    <script>
        function calculateTotal() {
            let total = 0;

            @if(isset($product))
                const price = {{ $product->price }};
                const qty = parseInt(document.getElementById('quantity').value || 0);
                total = price * qty;
            @elseif(isset($cartItems))
                document.querySelectorAll('[id^="quantity_"]').forEach(input => {
                    let price = parseFloat(input.closest('.flex-grow').querySelector('.text-lg.font-semibold').innerText.replace('₱', ''));
                    total += price * parseInt(input.value || 0);
                });
            @endif

            document.getElementById('total-price').innerText = total.toFixed(2);
        }

        document.querySelectorAll('input[type="number"]').forEach(input => {
            input.addEventListener('input', calculateTotal);
        });

        calculateTotal();
    </script>
</x-layout>
