<x-layout title="Checkout">
    <style>
        body {
            position: relative;
            min-height: 100vh;
            margin: 0;
        }

        body::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url('{{ asset('images/BG-2.jpg') }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            opacity: 0.3;
            z-index: -1;
        }
    </style>

    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4 text-center">🛒 Checkout</h1>

        <form action="{{ route('checkout.process') }}" method="POST" enctype="multipart/form-data">
            @csrf

            {{-- Total Items + Total Price --}}
            @if(isset($product) || (isset($cartItems) && count($cartItems) > 0))
            <div class="bg-white rounded-lg shadow-lg p-4 w-full mb-4 flex flex-col sm:flex-row justify-between items-center sm:items-end">
                <p class="text-lg font-semibold sm:text-left text-center w-full sm:w-1/2 mb-2 sm:mb-0">
                    Total Items: <span id="total-items">0</span>
                </p>
                <div class="text-center sm:text-right w-full sm:w-1/2">
                    <p class="text-xl font-bold">Total: ₱<span id="total-price">0.00</span></p>
                    <button type="submit" class="bg-green-500 text-white px-6 py-2 rounded-lg hover:bg-green-600 mt-2 sm:mt-1">
                        Buy Now
                    </button>
                </div>
            </div>
            @endif

            <div class="flex flex-col lg:flex-row gap-4">
                {{-- Product Section --}}
                <div class="w-full lg:w-1/2">
                    @if(isset($product))
                        <input type="hidden" name="type" value="single">
                        <input type="hidden" name="product_id" value="{{ $product->id }}">

                        <div class="bg-white rounded-lg shadow-lg p-4 flex flex-col sm:flex-row items-center mb-4">
                            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-full sm:w-[180px] h-auto object-cover rounded">
                            <div class="sm:ml-4 mt-4 sm:mt-0 flex-grow w-full">
                                <h2 class="text-lg font-bold">{{ $product->name }}</h2>
                                <p class="text-gray-600">{{ $product->description }}</p>
                                <p class="text-lg font-semibold">₱{{ $product->price }}</p>

                                <div class="mt-2">
                                    <label class="font-semibold">Select Size:</label><br>
                                    @foreach ($product->stocks->where('stock', '>', 0) as $stock)
                                        <label class="inline-flex items-center mr-4 mt-1">
                                            <input type="radio" name="size" value="{{ $stock->size }}" data-price="{{ $product->price }}" data-stock="{{ $stock->stock }}" required class="mr-1 size-radio-single">
                                            {{ strtoupper($stock->size) }} ({{ $stock->stock }})
                                        </label>
                                    @endforeach
                                </div>

                                <div class="mt-2">
                                    <label for="quantity" class="font-semibold">Quantity:</label>
                                    <input type="number" name="quantity" id="quantity-single" value="1" min="1" class="border px-2 py-1 rounded w-20 ml-2" max="1">
                                </div>
                            </div>
                        </div>

                    @elseif(isset($cartItems) && count($cartItems) > 0)
                        <input type="hidden" name="type" value="cart">
                        @foreach($cartItems as $item)
                        <div class="bg-white rounded-lg shadow-lg p-4 flex flex-col sm:flex-row items-center mb-4">
                            <img src="{{ asset('storage/' . $item->product->image) }}" alt="{{ $item->product->name }}" class="w-full sm:w-[180px] h-auto object-cover rounded">
                            <div class="sm:ml-4 mt-4 sm:mt-0 flex-grow w-full">
                                <h2 class="text-lg font-bold">{{ $item->product->name }}</h2>
                                <p class="text-gray-600">{{ $item->product->description }}</p>
                                <p class="text-lg font-semibold">₱{{ $item->product->price }}</p>

                                <input type="hidden" name="items[{{ $item->id }}][product_id]" value="{{ $item->product->id }}">

                                <label class="block mt-2 font-semibold">Select Size:</label>
                                @foreach ($item->product->stocks->where('stock', '>', 0) as $stock)
                                    <label class="inline-flex items-center mr-4 mt-1">
                                        <input type="radio" name="items[{{ $item->id }}][size]" value="{{ $stock->size }}" data-price="{{ $item->product->price }}" data-stock="{{ $stock->stock }}" class="mr-1 size-radio" data-item="{{ $item->id }}" required>
                                        {{ strtoupper($stock->size) }} ({{ $stock->stock }})
                                    </label>
                                @endforeach

                                <label class="block mt-2 font-semibold">Quantity:</label>
                                <input type="number" name="items[{{ $item->id }}][quantity]" id="quantity_{{ $item->id }}" value="1" min="1" class="border rounded px-2 py-1 w-20 quantity-input" data-item="{{ $item->id }}" max="1">
                            </div>
                        </div>
                        @endforeach
                    @else
                        <p class="text-gray-500 text-center">No items selected for checkout.</p>
                    @endif
                </div>

                {{-- Address & GCash --}}
                <div class="w-full lg:w-1/2 flex flex-col gap-4">
                    {{-- Address --}}
                    <div class="bg-white rounded-lg shadow-lg p-4">
                        <h2 class="text-lg font-bold mb-2">Shipping Address</h2>
                        <div class="grid grid-cols-1 gap-4">
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

                    {{-- GCash --}}
                    <div class="bg-white rounded-lg shadow-lg p-4">
                        <h2 class="text-lg font-bold mb-2 text-center">Pay with GCash</h2>

                        @if(isset($gcash) && $gcash->image_path && file_exists(storage_path('app/public/' . $gcash->image_path)))
                            <img src="{{ asset('storage/' . $gcash->image_path) }}" alt="GCash QR" class="w-full max-h-[300px] object-contain mb-2" />
                        @else
                            <p class="text-center text-sm">GCash image not available</p>
                        @endif

                        <label for="payment_proof" class="font-semibold block text-center mt-2">Upload Proof of Payment:</label>
                        <input type="file" name="payment_proof" id="payment_proof" accept="image/*" required class="mt-2 border px-2 py-1 rounded w-full">
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- JavaScript --}}
    <script>
        function updateTotalAndCount() {
            let total = 0;
            let itemCount = 0;

            // Single item
            const singleSize = document.querySelector('input[name="size"]:checked');
            const singleQty = document.getElementById("quantity-single");
            if (singleSize && singleQty) {
                const price = parseFloat(singleSize.getAttribute('data-price'));
                const stock = parseInt(singleSize.getAttribute('data-stock'));
                singleQty.max = stock;
                const qty = parseInt(singleQty.value || 1);
                total += price * qty;
                itemCount += qty;
            }

            // Cart items
            document.querySelectorAll('.size-radio').forEach(radio => {
                if (radio.checked) {
                    const itemId = radio.getAttribute('data-item');
                    const qtyInput = document.getElementById(`quantity_${itemId}`);
                    const stock = parseInt(radio.getAttribute('data-stock'));
                    qtyInput.max = stock;
                    const price = parseFloat(radio.getAttribute('data-price'));
                    const qty = parseInt(qtyInput.value || 1);
                    total += price * qty;
                    itemCount += qty;
                }
            });

            document.getElementById('total-price').innerText = total.toFixed(2);
            document.getElementById('total-items').innerText = itemCount;
        }

        document.querySelectorAll('input[type="radio"], input[type="number"]').forEach(input => {
            input.addEventListener('input', updateTotalAndCount);
            input.addEventListener('change', updateTotalAndCount);
        });

        updateTotalAndCount();
    </script>
</x-layout>
