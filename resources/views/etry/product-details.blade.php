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

    <div class="min-h-screen bg-cover bg-center bg-no-repeat bg-fixed" style="background-image: url({{ asset('images/BG.png') }})">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 w-full">
            <h1 class="text-3xl font-bold mb-6 pt-8 text-center">Product Details</h1>
            <!-- Product Card -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-stretch">
                <!-- Left: Product Card -->
                <div class="bg-white rounded-lg shadow-2xl p-6 md:p-8 grid grid-cols-1 gap-6 items-stretch h-full">
                    <!-- Product Image -->
                    <div class="relative w-full flex items-center justify-center overflow-hidden h-[380px] sm:h-[420px] md:h-[480px] rounded-lg">
                        <img src="{{ route('files.public', ['path' => $product->image]) }}"
                            alt="{{ $product->name }}"
                            class="w-full h-full object-contain rounded-lg transition-transform duration-300 ease-out hover:scale-110 cursor-zoom-in" />
                    </div>
                    
                    <!-- Product Details -->
                    <div class="flex flex-col gap-4">
                        <h2 class="text-2xl md:text-3xl font-bold">{{ $product->name }}</h2>
                        <p class="text-gray-700 leading-relaxed">{{ $product->description }}</p>
                        
                        <!-- Rating Display -->
                        <div class="flex items-center space-x-3">
                            <div class="flex items-center">
                                @php
                                    $avg = $averageRating ?? 0;
                                    $full = floor($avg);
                                    $half = ($avg - $full) >= 0.5;
                                @endphp
                                @for($i = 1; $i <= 5; $i++)
                                    @if($i <= $full)
                                        <span class="text-yellow-500 text-xl">★</span>
                                    @elseif($i === ($full + 1) && $half)
                                        <span class="relative inline-block text-xl">
                                            <span class="text-gray-300">★</span>
                                            <span class="absolute inset-0 w-1/2 overflow-hidden text-yellow-500">★</span>
                                        </span>
                                    @else
                                        <span class="text-gray-300 text-xl">★</span>
                                    @endif
                                @endfor
                            </div>
                            <div>
                                <span class="font-semibold">{{ number_format($averageRating ?? 0, 1) }}</span>
                                <span class="text-gray-600">({{ $reviewsCount ?? 0 }} reviews)</span>
                            </div>
                        </div>
    
                        <div class="flex items-center justify-between">
                            <p class="text-2xl font-semibold">₱{{ $product->price }}</p>
    
                            <div class="icons-container">
                                <button onclick="toggleCartIcon(this,{{ $product->id }})">
                                    <img src="{{ in_array($product->id, $cartItemIds) ? asset('icons/addtocart-on.svg') : asset('icons/addtocart-off.svg') }}"
                                        alt="add-to-cart"
                                        class="w-6 h-6 sm:w-7 sm:h-7 cursor-pointer cart-icon transition-transform duration-200 ease-in-out object-contain">
                                </button>
                                <button>
                                    <img src="{{ asset('icons/camera.svg') }}" alt="TRY-ON" class="w-6 h-6 sm:w-7 sm:h-7 cursor-pointer">
                                </button>
                            </div>
                        </div>
                        
                        @if($stocks->isNotEmpty())
                            <div class="mt-3">
                                <p class="font-semibold mb-2">Available Sizes</p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($stocks as $stock)
                                        @if($stock->stock > 0)
                                            <span class="px-3 py-1 border border-gray-300 rounded-full text-sm text-gray-700">
                                                Size: <strong>{{ $stock->size }}</strong> — Stock: <strong>{{ $stock->stock }}</strong>
                                            </span>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <p class="text-red-500 mt-3">Out of stock</p>
                        @endif
                    </div>
                </div>
    
                <!-- Right: Measurement Guide -->
                @if($product->measurement_image)
                    <div class="bg-white rounded-lg shadow-lg p-4 md:p-6 h-full">
                        <h3 class="text-xl font-bold mb-3 text-center">Measurement Guide</h3>
                        <div class="relative w-full h-[380px] sm:h-[420px] md:h-[480px] overflow-hidden rounded border border-gray-300 flex items-center justify-center">
                            <img src="{{ route('files.public', ['path' => $product->measurement_image]) }}" 
                                alt="Measurement Image" class="max-h-full max-w-full object-contain">
                        </div>
                    </div>
                @endif
            </div>
        </div>
            <!-- Checkout Button -->
            <div class="flex justify-center mt-8">
                <a href="{{ route('checkout.index', ['productId' => $product->id]) }}"
                class="bg-blue-500 text-white py-3 px-6 mb-8 rounded-lg hover:bg-blue-600 text-center w-full sm:w-1/2 md:w-1/3">
                    Proceed to Checkout
                </a>
            </div>

            <div id="cart-notification" class="cart-overlay">
                Item updated successfully!
            </div>

            <!-- Reviews Section -->
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
                <div class="bg-white rounded-lg shadow-lg p-6 mb-12">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-bold">Customer Reviews</h3>
                    </div>
                    @if(isset($reviews) && $reviews->count() > 0)
                        <div class="space-y-4">
                            @foreach($reviews as $review)
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-2">
                                            <span class="text-yellow-500">★</span>
                                            <span class="font-semibold">{{ number_format($review->rating, 1) }}</span>
                                        </div>
                                        @php
                                            $reviewerName = $review->user->name ?? 'Anonymous';
                                            if (!empty($review->is_anonymous) && $review->user && $review->user->name) {
                                                $reviewerName = \Illuminate\Support\Str::mask($review->user->name, '*', 2);
                                            }
                                        @endphp
                                        <span class="text-gray-500 text-sm">by {{ $reviewerName }} • {{ $review->created_at->diffForHumans() }}</span>
                                    </div>
                                    @if($review->comment)
                                        <p class="mt-2 text-gray-700">{{ $review->comment }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-4">
                            {{ $reviews->links('pagination::tailwind') }}
                        </div>
                    @else
                        <p class="text-gray-600">No reviews yet. Be the first to review this product!</p>
                    @endif
                </div>
            </div>
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
