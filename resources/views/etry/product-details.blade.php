<x-layout title="Product Details">
    <style>
        .scale-110 {
            transform: scale(1.1);
        }
        .cart-overlay {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(0, 0, 0, 0.7);
            color: #fff;
            padding: 20px 40px;
            border-radius: 10px;
            z-index: 9999;
            display: none;
        }
        .overlay-blur {
            backdrop-filter: blur(5px);
        }
        .icons-container {
            display: flex;
            align-items: center;
            gap: 10px;
        }
    </style>

    <div class="container mx-auto p-4">
        <div class="bg-white rounded-lg shadow-lg p-4 relative">
            <!-- Product Image -->
            <img src="{{ asset('storage/' . $product->image) }}"
                 alt="{{ $product->name }}"
                 class="w-full h-64 object-contain rounded-t-lg">

            <!-- Product Details -->
            <div class="p-4">
                <h2 class="text-2xl font-bold">{{ $product->name }}</h2>
                <p class="text-gray-600">{{ $product->description }}</p>

                <div class="flex items-center justify-between">
                    <p class="text-lg font-semibold">₱{{ $product->price }}</p>

                    <!-- Icons beside the price -->
                    <div class="icons-container">
                        <!-- Add to Cart Button -->
                        <button onclick="toggleCartIcon(this,{{ $product->id }})">
                            <img src="{{ in_array($product->id, $cartItemIds) ? asset('icons/addtocart-on.svg') : asset('icons/addtocart-off.svg') }}"
                                 alt="add-to-cart"
                                 class="w-6 h-6 sm:w-7 sm:h-7 cursor-pointer cart-icon transition-transform duration-200 ease-in-out object-contain">
                        </button>
                        <!-- Try-On Button -->
                        <button>
                            <img src="{{ asset('icons/camera.svg') }}" alt="TRY-ON" class="w-6 h-6 sm:w-7 sm:h-7 cursor-pointer">
                        </button>
                    </div>
                </div>
                
                @if($stocks->isNotEmpty())
                    <div class="mt-3">
                        <p class="font-semibold">Available Sizes:</p>
                        @foreach($stocks as $stock)
                            @if($stock->stock > 0)
                                <p class="text-gray-600">Size: <strong>{{ $stock->size }}</strong> - Stock: <strong>{{ $stock->stock }}</strong></p>
                            @endif
                        @endforeach
                    </div>
                @else
                    <p class="text-red-500">Out of stock</p>
                @endif

                <!-- Add to Cart Button -->
                <a href="{{ route('checkout.index', ['productId' => $product->id]) }}"
                   class="bg-blue-500 text-white w-full py-2 rounded-lg hover:bg-blue-600 mt-4 text-center block">
                    Checkout
                </a>
            </div>
        </div>
    </div>

    <div id="cart-notification" class="cart-overlay">
        Item updated successfully!
    </div>

    <!-- JavaScript -->
    <script>
        function toggleCartIcon(button, productId) {
            const img = button.querySelector('.cart-icon');

            fetch(`/cart/add/${productId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({}) 
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    img.src = data.added
                        ? "{{ asset('icons/addtocart-on.svg') }}"
                        : "{{ asset('icons/addtocart-off.svg') }}";
                    img.classList.add('scale-110');
                    setTimeout(() => img.classList.remove('scale-110'), 200);

                    // Show Overlay Notification
                    const notification = document.getElementById('cart-notification');
                    notification.textContent = data.added ? 'Product added to cart!' : 'Product removed from cart.';
                    notification.classList.add('overlay-blur');
                    notification.style.display = 'block';
                    setTimeout(() => {
                        notification.style.display = 'none';
                        notification.classList.remove('overlay-blur');
                    }, 2000);
                }
            })
            .catch(error => console.error('Error:', error));
        }
    </script>
</x-layout>
