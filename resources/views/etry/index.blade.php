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
                        <button
                            onclick="openEditModal(this)"
                            class="absolute top-2 right-14 bg-white rounded-full p-1 shadow-md hidden group-hover:inline-block z-10 transition cursor-pointer"
                            data-id="{{ $product->id }}"
                            data-name="{{ $product->name }}"
                            data-description="{{ $product->description }}"
                            data-price="{{ $product->price }}"
                            data-sizes='@json($product->sizes)'
                            data-image="{{ $product->image }}"
                        >
                            <img src="{{ asset('icons/editbutton.svg') }}" class="w-5 h-5" alt="Edit">
                        </button>

                    @endif

                    <!-- Image -->
                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-full h-40 sm:h-56 object-contain rounded-t-lg">

                    <!-- Product Info -->
                    <div class="p-4 flex-grow flex justify-between items-start">
                        <div class="w-3/4">
                            <h2 class="text-lg md:text-xl font-bold">{{ $product->name }}</h2>
                            <p class="text-gray-600 text-sm md:text-base">
                                {{ \Illuminate\Support\Str::words($product->description, 4, '...') }}
                            </p>
                            

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
                               @php
                                    $productId = $product->id;
                                    $productFolderPath = public_path("ar/product{$productId}");

                                    $testFilePath = '';

                                    if (is_dir($productFolderPath)) {
                                        $subfolders = array_filter(glob($productFolderPath . '/*'), 'is_dir');

                                        foreach ($subfolders as $subfolder) {
                                            $possibleTestFile = $subfolder . '/test.html'; // Look directly here
                                            if (file_exists($possibleTestFile)) {
                                                $testFilePath = $possibleTestFile;
                                                break;
                                            }
                                        }
                                    }
                                @endphp

                                @if($testFilePath)
                                    <a href="{{ route('tryon.test', ['id' => $productId]) }}" target="_blank" title="Try On">
                                        <img src="{{ asset('icons/camera.svg') }}" alt="TRY-ON" class="w-6 h-6 sm:w-7 sm:h-7 cursor-pointer">
                                    </a>
                                @endif



                            </div>
                        </div>
                    </div>

                    <!-- Buy Button or Out of Stock Message -->
                    @if($outOfStock)
                        <span class="bg-red-500 text-white w-full py-2 rounded-lg mt-auto text-sm md:text-base text-center block">Out of Stock</span>
                    @else
                        <a href="{{ route('products.show', $product->id) }}" class="bg-white text-black border-2 w-full py-2 rounded-lg hover:bg-[#FAC000] mt-auto text-sm md:text-base text-center block duration-100">Buy Now</a>
                    @endif
                </div>
            @endforeach
        </div>

        <!-- No Products Found Message -->
        @if($products->isEmpty())
            <div class="col-span-4 text-center mt-4">
                <p class="text-gray-500">No products found.</p>
            </div>
        @endif

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
    <div id="edit-product-modal" class="fixed inset-0  bg-opacity-50 hidden justify-center items-center z-50">
        <div class="rounded-lg bg-gray-800 text-white shadow-lg p-6 text-center max-w-sm w-full relative">
            <h2 class="text-xl font-semibold mb-4">Edit Product</h2>

            <form id="edit-product-form" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- Hidden input for product ID -->
                <input type="hidden" id="product-id" name="product_id">

                <!-- Product Name -->
                <div class="mb-4">
                    <label for="product-name" class="block text-sm">Name</label>
                    <input type="text" id="product-name" name="name" class="w-full p-2 rounded border" required>
                </div>

                <!-- Description -->
                <div class="mb-4">
                    <label for="product-description" class="block text-sm">Description</label>
                    <textarea id="product-description" name="description" class="w-full p-2 rounded border" required></textarea>
                </div>

                <!-- Price -->
                <div class="mb-4">
                    <label for="product-price" class="block text-sm">Price</label>
                    <input type="number" id="product-price" name="price" class="w-full p-2 rounded border" step="0.01" required>
                </div>

                <!-- Sizes & Stock -->
                <div class="mb-4">
                    <label class="block text-sm mb-2">Sizes & Stock</label>
                    <div id="sizes-wrapper" class="space-y-2">
                       @foreach(['S', 'M', 'L', 'XL'] as $size)
                            <div class="flex items-center space-x-2">
                                <input type="checkbox" id="size_{{ $size }}" name="sizes[]" value="{{ $size }}" onclick="toggleStockInput('{{ $size }}')">
                                <label for="size_{{ $size }}" class="flex-grow">{{ $size }}</label>
                                <input type="number" name="stock[{{ $size }}]" id="stock_{{ $size }}" placeholder="Stock for {{ $size }}"
                                    class="ml-4 px-2 py-1 border rounded w-32 hidden" min="0">
                            </div>
                        @endforeach
                    </div>
                </div>


                <!-- Image Preview -->
                <div class="mb-4">
                    <label for="image" class="block text-sm">Image</label>
                    <img id="product-image-preview" class="mb-2 w-full h-40 object-contain rounded" src="" alt="Product Image">
                    <input type="file" name="image" id="image" class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-1 cursor-pointer hover:border-blue-500">
                </div>

                <!-- Buttons -->
                <div class="flex justify-center space-x-4">
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Save</button>
                    <button type="button" onclick="closeEditModal()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">Cancel</button>
                </div>
            </form>
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
            const checkbox = document.getElementById('size_' + size);
            const input = document.getElementById('stock_' + size);
            input.classList.toggle('hidden', !checkbox.checked);
        }


        function openEditModal(button) {
            const id = button.getAttribute('data-id');
            const name = button.getAttribute('data-name');
            const description = button.getAttribute('data-description');
            const price = button.getAttribute('data-price');
            const image = button.getAttribute('data-image');
            const sizes = JSON.parse(button.getAttribute('data-sizes') || '{}');

            // Set form action dynamically
            const form = document.getElementById('edit-product-form');
            form.action = `/products/${id}`;

            // Fill form fields
            document.getElementById('product-id').value = id;
            document.getElementById('product-name').value = name;
            document.getElementById('product-description').value = description;
            document.getElementById('product-price').value = price;

            // Handle sizes
            ['S', 'M', 'L', 'XL'].forEach(size => {
                const checkbox = document.getElementById('size_' + size);
                const input = document.getElementById('stock_' + size);

                if (sizes.hasOwnProperty(size)) {
                    checkbox.checked = true;
                    input.classList.remove('hidden');
                    input.value = sizes[size];
                } else {
                    checkbox.checked = false;
                    input.classList.add('hidden');
                    input.value = '';
                }
            });

            // Image
            const imagePreview = document.getElementById('product-image-preview');
            if (image) {
                imagePreview.src = `/storage/${image}`;
                imagePreview.classList.remove('hidden');
            } else {
                imagePreview.classList.add('hidden');
            }

            // Show modal
            const modal = document.getElementById('edit-product-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeEditModal() {
            document.getElementById('edit-product-modal').classList.add('hidden');
        }

    </script>
</x-layout>
