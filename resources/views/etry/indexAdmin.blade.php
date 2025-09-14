<x-layout title="Admin - Products">
    @php
        $sizeNames = ['S'=>'Small','M'=>'Medium','L'=>'Large','XL'=>'XL'];
    @endphp

    <style>
        .modal {
            position: fixed;
            inset: 0;
            display: none;
            justify-content: center;
            align-items: center;
            background: rgba(0,0,0,0.5);
            z-index: 50;
        }
        .modal.active { display: flex; }

        body::before {
            content: "";
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background-image: url('{{ asset('images/BG-1.jpg') }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            opacity: 0.2;
            z-index: -1;
        }

        table {
            background-color: #ffffff;
        }

        th, td {
            background-color: #ffffff;
        }

        tr:nth-child(even) td {
            background-color: #f9f9f9;
        }
    </style>

    <div class="container mx-auto p-6">
        <h1 class="text-2xl font-bold mb-6">Admin - Product Management</h1>

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

        <table class="w-full border-collapse border border-gray-300 shadow-md">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border border-gray-300 px-4 py-2">Image</th>
                    <th class="border border-gray-300 px-4 py-2">Name</th>
                    <th class="border border-gray-300 px-4 py-2">Description</th>
                    <th class="border border-gray-300 px-4 py-2">Price</th>
                    <th class="border border-gray-300 px-4 py-2">Sizes</th>
                    @if(auth()->check() && auth()->user()->id == 1)
                        <th class="border border-gray-300 px-4 py-2">Actions</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                    @php
                        $sizes = json_decode($product->sizes, true) ?? [];
                        $availableSizes = [];
                        $outOfStock = true;
                        $sizeStocks = [];

                        foreach ($sizes as $size) {
                            $sizeValue = is_array($size) ? $size['size'] ?? null : $size;
                            if ($sizeValue) {
                                $stock = \App\Models\ProductStock::where('product_id', $product->id)
                                    ->where('size', $sizeValue)
                                    ->sum('stock');
                                $sizeStocks[$sizeValue] = $stock;
                                if ($stock > 0) {
                                    $availableSizes[] = $sizeValue;
                                    $outOfStock = false;
                                }
                            }
                        }
                    @endphp
                    <tr>
                        <td class="border border-gray-300 px-4 py-2 text-center">
                            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-20 h-20 object-contain mx-auto">
                        </td>
                        <td class="border border-gray-300 px-4 py-2">{{ $product->name }}</td>
                        <td class="border border-gray-300 px-4 py-2">{{ \Illuminate\Support\Str::words($product->description, 10, '...') }}</td>
                        <td class="border border-gray-300 px-4 py-2">₱{{ $product->price }}</td>
                        <td class="border border-gray-300 px-4 py-2">
                            @foreach($availableSizes as $size)
                                <span class="inline-block bg-gray-200 rounded px-2 py-1 text-xs mr-1">
                                    {{ $sizeNames[$size] ?? $size }}: {{ $sizeStocks[$size] }}
                                </span>
                            @endforeach
                            @if($outOfStock)
                                <span class="inline-block bg-red-500 text-white px-2 py-1 rounded text-xs">Out of Stock</span>
                            @endif
                        </td>
                        @if(auth()->check() && auth()->user()->id == 1)
                            <td class="border border-gray-300 px-4 py-2 text-center space-x-2">
                                <button class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600"
                                        onclick="openEditModal('{{ $product->id }}','{{ $product->name }}','{{ $product->description }}','{{ $product->price }}','{{ $product->image }}',@json($sizeStocks))">
                                    Edit
                                </button>
                                <button class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600"
                                        onclick="openDeleteModal({{ $product->id }})">
                                    Delete
                                </button>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-gray-500">No products found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="mt-6">
            {{ $products->links('pagination::tailwind') }}
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="edit-modal" class="modal">
        <div class="bg-white rounded-lg p-6 w-full max-w-md relative">
            <h2 class="text-xl font-bold mb-4">Edit Product</h2>
            <form id="edit-product-form" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <input type="hidden" id="product-id" name="product_id">

                <div class="mb-3">
                    <label class="block text-sm">Name</label>
                    <input type="text" id="product-name" name="name" class="w-full border p-2 rounded" required>
                </div>

                <div class="mb-3">
                    <label class="block text-sm">Description</label>
                    <textarea id="product-description" name="description" class="w-full border p-2 rounded" required></textarea>
                </div>

                <div class="mb-3">
                    <label class="block text-sm">Price</label>
                    <input type="number" id="product-price" name="price" class="w-full border p-2 rounded" step="0.01" required>
                </div>

                <div class="mb-3">
                    <label class="block text-sm">Sizes & Stock</label>
                    <div id="sizes-wrapper" class="space-y-2">
                       @foreach($sizeNames as $key => $label)
                           <div class="flex items-center space-x-2">
                               <input type="checkbox" id="size_{{ $key }}" name="sizes[]" value="{{ $key }}" onclick="toggleStockInput('{{ $key }}')">
                               <label for="size_{{ $key }}" class="flex-grow">{{ $label }}</label>
                               <input type="number" name="stock[{{ $key }}]" id="stock_{{ $key }}" placeholder="Stock for {{ $label }}"
                                      class="ml-4 px-2 py-1 border rounded w-32 hidden" min="0">
                           </div>
                       @endforeach
                    </div>
                </div>

                <div class="mb-3">
                    <label class="block text-sm">Image</label>
                    <img id="product-image-preview" class="w-full h-40 object-contain mb-2" src="" alt="">
                    <input type="file" name="image" class="w-full border p-2 rounded">
                </div>

                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="closeModal('edit-modal')" class="bg-gray-300 px-3 py-1 rounded">Cancel</button>
                    <button type="submit" class="bg-blue-500 text-white px-3 py-1 rounded">Save</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Modal -->
    <div id="delete-modal" class="modal">
        <div class="bg-white rounded-lg p-6 w-full max-w-sm text-center">
            <h2 class="text-lg font-bold mb-4">Are you sure?</h2>
            <div class="flex justify-center space-x-2">
                <form id="delete-form" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-500 text-white px-3 py-1 rounded">Delete</button>
                </form>
                <button onclick="closeModal('delete-modal')" class="bg-gray-300 px-3 py-1 rounded">Cancel</button>
            </div>
        </div>
    </div>

    <script>
        function toggleStockInput(size) {
            const checkbox = document.getElementById('size_' + size);
            const input = document.getElementById('stock_' + size);
            input.classList.toggle('hidden', !checkbox.checked);
        }

        function openEditModal(id, name, desc, price, image, sizes) {
            const form = document.getElementById('edit-product-form');
            form.action = `/products/${id}`;
            document.getElementById('product-id').value = id;
            document.getElementById('product-name').value = name;
            document.getElementById('product-description').value = desc;
            document.getElementById('product-price').value = price;
            document.getElementById('product-image-preview').src = '/storage/' + image;

            Object.keys({!! json_encode($sizeNames) !!}).forEach(size => {
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

            document.getElementById('edit-modal').classList.add('active');
        }

        function openDeleteModal(id) {
            const form = document.getElementById('delete-form');
            form.action = `/products/delete/${id}`;
            document.getElementById('delete-modal').classList.add('active');
        }

        function closeModal(id) {
            document.getElementById(id).classList.remove('active');
        }
    </script>
</x-layout>
