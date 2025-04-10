<x-layout title="Products">
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
    </style>

    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div id="cart-notification" class="cart-overlay">Item updated successfully!</div>

        <!-- Sorting & Gender Filter -->
        <form method="GET" class="mb-4 flex items-center space-x-2">
            <label for="sort" class="text-sm md:text-base">Sort by:</label>
            <select name="sort" id="sort" onchange="this.form.submit()" class="border rounded-lg px-3 py-1 text-sm md:text-base">
                <option value="desc" {{ $sortOption == 'desc' ? 'selected' : '' }}>Newest First</option>
                <option value="asc" {{ $sortOption == 'asc' ? 'selected' : '' }}>Oldest First</option>
                <option value="price_asc" {{ $sortOption == 'price_asc' ? 'selected' : '' }}>Price: Low to High</option>
                <option value="price_desc" {{ $sortOption == 'price_desc' ? 'selected' : '' }}>Price: High to Low</option>
            </select>

            <label for="gender" class="text-sm md:text-base">Filter by Gender:</label>
            <select name="gender" id="gender" onchange="this.form.submit()" class="border rounded-lg px-3 py-1 text-sm md:text-base">
                <option value="" {{ request('gender') == '' ? 'selected' : '' }}>All</option>
                <option value="Men" {{ request('gender') == 'Men' ? 'selected' : '' }}>Men</option>
                <option value="Women" {{ request('gender') == 'Women' ? 'selected' : '' }}>Women</option>
            </select>
        </form>

        <!-- Product Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach($products as $product)
                <div class="bg-white rounded-lg shadow-lg p-4 flex flex-col h-full hover:drop-shadow-2xl transition duration-300 relative group">
                    @if(auth()->check() && auth()->user()->id == 1)
                        <!-- Edit and Delete Icons (Only for Admin) -->
                        <button onclick="showDeleteModal({{ $product->id }})" class="absolute top-2 right-2 bg-white rounded-full p-1 shadow-md hidden group-hover:inline-block z-10 transition cursor-pointer">
                            <img src="{{ asset('icons/delete-1-svgrepo-com.svg') }}" class="w-5 h-5" alt="Delete">
                        </button>
                        <button onclick="openEditModal({{ $product->id }})" class="absolute top-2 right-14 bg-white rounded-full p-1 shadow-md hidden group-hover:inline-block z-10 transition cursor-pointer">
                            <img src="{{ asset('icons/editbutton.svg') }}" class="w-5 h-5" alt="Edit">
                        </button>
                    @endif

                    <!-- Image -->
                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-full h-40 sm:h-56 object-contain rounded-t-lg">

                    <!-- Product Info -->
                    <div class="p-4 flex-grow flex justify-between items-start">
                        <div class="w-3/4">
                            <h2 class="text-lg md:text-xl font-bold">{{ $product->name }}</h2>
                            <p class="text-gray-600 text-sm md:text-base">{{ $product->description }}</p>

                            @php
                                $sizes = json_decode($product->sizes, true);
                                $availableSizes = [];
                                $outOfStock = true;

                                foreach ($sizes as $size) {
                                    $sizeValue = is_array($size) ? $size['size'] ?? null : $size;
                                    if ($sizeValue) {
                                        $stock = \App\Models\ProductStock::where('product_id', $product->id)
                                            ->where('size', $sizeValue)
                                            ->sum('stock');

                                        if ($stock > 0) {
                                            $availableSizes[] = $sizeValue;
                                            $outOfStock = false;
                                        }
                                    }
                                }
                            @endphp

                            @if(!empty($availableSizes))
                            <p class="text-gray-500 text-xs md:text-sm mt-1">Sizes:
                                @foreach($availableSizes as $size)
                                    <span class="inline-block bg-gray-200 rounded px-2 py-1 text-xs mr-1">{{ $size }}</span>
                                @endforeach
                            </p>
                            @endif
                        </div>

                        <div class="w-1/4 text-right">
                            <p class="text-lg font-semibold">₱{{ $product->price }}</p>
                            <div class="flex justify-end space-x-3 mt-2">
                                <!-- Add to Cart Button -->
                                <button onclick="toggleCartIcon(this, {{ $product->id }})">
                                    <img src="{{ in_array($product->id, $cartItemIds) ? asset('icons/addtocart-on.svg') : asset('icons/addtocart-off.svg') }}" 
                                        alt="add-to-cart" class="w-6 h-6 sm:w-7 sm:h-7 cursor-pointer cart-icon transition-transform duration-200 ease-in-out object-contain">
                                </button>
                                <!-- Try-On Button -->
                                <button>
                                    <img src="{{ asset('icons/camera.svg') }}" alt="TRY-ON" class="w-6 h-6 sm:w-7 sm:h-7 cursor-pointer">
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Buy Button or Out of Stock Message -->
                    @if($outOfStock)
                        <span class="bg-red-500 text-white w-full py-2 rounded-lg mt-auto text-sm md:text-base text-center block">Out of Stock</span>
                    @else
                        <a href="{{ route('products.show', $product->id) }}" class="bg-blue-500 text-white w-full py-2 rounded-lg hover:bg-blue-600 mt-auto text-sm md:text-base text-center block">Buy Now</a>
                    @endif
                </div>
            @endforeach

        </div>

        <!-- Pagination Links -->
        <div class="mt-6">
            {{ $products->links('pagination::tailwind') }}
        </div>
    </div>

    <!-- Delete Confirmation -->
    <div id="delete-confirmation-modal" class="fixed inset-0 bg-opacity-50 justify-center items-center z-50 hidden">
        <div class="rounded-lg bg-gray-800 text-white shadow-lg p-6 text-center max-w-sm w-full">
            <h2 class="text-xl font-semibold mb-4">Are you sure you want to delete this product?</h2>
            <div class="flex justify-center space-x-4">
                <button onclick="confirmDelete()" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">Delete</button>
                <button onclick="closeModal()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">Cancel</button>
            </div>
        </div>
    </div>

    <!-- Edit Product Modal -->
    <div id="edit-product-modal" class="fixed inset-0 bg-opacity-50 justify-center items-center z-50 hidden">
        <div class="rounded-lg bg-gray-800 text-white shadow-lg p-6 text-center max-w-sm w-full">
            <h2 class="text-xl font-semibold mb-4">Edit Product</h2>
            @if(isset($product))
            <form id="edit-product-form" action="{{ route('products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- Hidden input for product-id -->
                <input type="hidden" id="product-id" name="product_id" value="{{ old('product_id', $product->id) }}">

                <!-- Product Name -->
                <div class="mb-4">
                    <label for="product-name" class="block text-sm">Name</label>
                    <input type="text" id="product-name" name="name" class="w-full p-2 rounded border" value="{{ old('name', $product->name) }}" required>
                </div>

                <!-- Product Description -->
                <div class="mb-4">
                    <label for="product-description" class="block text-sm">Description</label>
                    <textarea id="product-description" name="description" class="w-full p-2 rounded border" required>{{ old('description', $product->description) }}</textarea>
                </div>

                <!-- Product Price -->
                <div class="mb-4">
                    <label for="product-price" class="block text-sm">Price</label>
                    <input type="number" id="product-price" name="price" class="w-full p-2 rounded border" value="{{ old('price', $product->price) }}" step="0.01" required>
                </div>

                <!-- Product Sizes & Stock -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Sizes & Stock</label>
                    <div id="sizes-wrapper" class="space-y-2">
                        @foreach(['S', 'M', 'L', 'XL'] as $size)
                            <div class="flex items-center space-x-2">
                                <input type="checkbox" id="size_{{ $size }}" name="sizes[]" value="{{ $size }}" 
                                    onclick="toggleStockInput('{{ $size }}')" 
                                    {{ in_array($size, old('sizes', json_decode($product->sizes, true))) ? 'checked' : '' }}>
                                <label for="size_{{ $size }}" class="flex-grow">{{ $size }}</label>
                                <input type="number" name="stock[{{ $size }}]" id="stock_{{ $size }}" placeholder="Stock for {{ $size }}"
                                    class="ml-4 px-2 py-1 border rounded w-32 {{ in_array($size, old('sizes', json_decode($product->sizes, true))) ? '' : 'hidden' }}"
                                    value="{{ old('stock.' . $size, isset($product->sizes[$size]) ? $product->sizes[$size] : '') }}" min="0">
                            </div>
                        @endforeach                    
                    </div>
                </div>

                <!-- Product Image -->
                <div class="mb-4">
                    <label for="image" class="block text-sm font-medium text-gray-700">Image</label>
                    <!-- Display current image if available -->
                    @if($product->image)
                        <div class="mb-2">
                            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-full h-40 sm:h-56 object-contain rounded-t-lg">
                        </div>
                    @endif
                    <input type="file" name="image" id="image" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 cursor-pointer hover:border-blue-500">
                </div>

                <!-- Submit Button -->
                <div class="flex justify-center space-x-4">
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Save Changes</button>
                    <button type="button" onclick="closeEditModal()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">Cancel</button>
                </div>
            </form>
            @else
                <p>Product not found.</p>
            @endif
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
            .then(response => {
                if (response.redirected) {
                    window.location.href = '/login'; 
                    return;
                }
                return response.json();
            })
            .then(data => {
                if (data && data.success) {
                    img.src = data.added ? "{{ asset('icons/addtocart-on.svg') }}" : "{{ asset('icons/addtocart-off.svg') }}";
                    img.classList.add('scale-110');
                    setTimeout(() => img.classList.remove('scale-110'), 200);
                    const notification = document.getElementById('cart-notification');
                    notification.textContent = data.added ? 'Product added to cart successfully!' : 'Product removed from cart.';
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

        // Delete Confirmation
        let productIdToDelete = null;
        function showDeleteModal(productId) {
            productIdToDelete = productId;
            document.getElementById('delete-confirmation-modal').classList.remove('hidden');
            document.getElementById('delete-confirmation-modal').classList.add('flex');
        }

        function closeModal() {
            productIdToDelete = null;
            document.getElementById('delete-confirmation-modal').classList.add('hidden');
        }

        function confirmDelete() {
            if (!productIdToDelete) return;
            fetch(`/products/delete/${productIdToDelete}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Something went wrong!');
                }
            });
            closeModal();
        }

        // Open the Edit Product Modal and fetch product data
        function toggleStockInput(size) {
            const stockInput = document.getElementById('stock_' + size);
            const checkbox = document.getElementById('size_' + size);
            if (checkbox.checked) {
                stockInput.classList.remove('hidden');
            } else {
                stockInput.classList.add('hidden');
            }
        }

        // Function to open the modal
        function openEditModal() {
            document.getElementById('edit-product-modal').classList.remove('hidden');
            document.getElementById('edit-product-modal').classList.add('flex');
        }

        // Function to close the modal
        function closeEditModal() {
            document.getElementById('edit-product-modal').classList.add('hidden');
        }

    </script>
</x-layout>
