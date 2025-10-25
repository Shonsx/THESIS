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
        <!-- Analytics Overview -->
        <div class="mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-red-300 shadow rounded p-4">
                    <div class="text-black text-sm">Current Online</div>
                    <div class="text-2xl font-bold" id="current-online-count">{{ $currentOnline }}</div>
                </div>
                <div class="bg-blue-300 shadow rounded p-4">
                    <div class="text-black text-sm">Today's Unique Visitors</div>
                    <div class="text-2xl font-bold">{{ $todayVisitors }}</div>
                </div>
                <div class="bg-orange-300 shadow rounded p-4">
                    <div class="text-black text-sm">Last Update</div>
                    <div class="text-sm">{{ now()->format('M d, Y H:i') }}</div>
                </div>
            </div>
            <div class="mt-4 bg-white shadow rounded p-4 overflow-x-auto">
                <h2 class="text-lg font-semibold mb-2">Daily Visitors (Last 30 Days)</h2>
                <table class="w-full border-collapse border border-gray-200">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="border border-gray-200 px-4 py-2 text-left">Date</th>
                            <th class="border border-gray-200 px-4 py-2 text-left">Visitors</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dailyHistory as $row)
                            <tr>
                                <td class="border border-gray-200 px-4 py-2">{{ \Carbon\Carbon::parse($row->visited_at)->format('M d, Y') }}</td>
                                <td class="border border-gray-200 px-4 py-2">{{ $row->count }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-center text-gray-500 px-4 py-2">No analytics data yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Sales Analysis -->
            <div class="mt-4 bg-white shadow rounded p-4 overflow-x-auto">
                <h2 class="text-lg font-semibold mb-2">Sales (Processed Orders)</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div class="bg-green-50 border border-green-200 rounded p-4">
                        <div class="text-gray-600 text-sm">Today's Processed Sales</div>
                        <div class="text-2xl font-bold">₱{{ number_format($todayProcessedSales ?? 0, 2) }}</div>
                    </div>
                </div>
                <table class="w-full border-collapse border border-gray-200">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="border border-gray-200 px-4 py-2 text-left">Date</th>
                            <th class="border border-gray-200 px-4 py-2 text-left">Total Sales (₱)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dailySalesHistory as $row)
                            <tr>
                                <td class="border border-gray-200 px-4 py-2">{{ \Carbon\Carbon::parse($row->date)->format('M d, Y') }}</td>
                                <td class="border border-gray-200 px-4 py-2">₱{{ number_format($row->total, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-center text-gray-500 px-4 py-2">No processed sales yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

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
                    <th class="border border-gray-300 px-4 py-2">Sizes & Stocks</th>
                    @if(auth()->check() && auth()->user()->role === 'admin')
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
                        @if(auth()->check() && auth()->user()->role === 'admin')
                            <td class="border border-gray-300 px-4 py-2 text-center space-x-2">
                                <!-- Edit Button -->
                                <button class="bg-black text-white px-3 py-1 my-2 rounded transition duration-500 hover:bg-[#FFD700]"
                                    data-id="{{ $product->id }}"
                                    data-name="{{ $product->name }}"
                                    data-desc="{{ $product->description }}"
                                    data-price="{{ $product->price }}"
                                    data-image="{{ $product->image }}"
                                    data-measurement="{{ $product->measurement_image }}"
                                    data-sizes='@json($sizeStocks)'
                                    onclick="openEditModal(this)">
                                    Edit
                                </button>

                                <!-- Delete Button -->
                                <button class="bg-red-500 text-white px-3 py-1 my-2 rounded transition duration-500ss hover:bg-[#FFD700]"
                                    data-id="{{ $product->id }}"
                                    onclick="openDeleteModal(this)">
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
        <div class="bg-white rounded-lg p-6 w-full max-w-md relative max-h-[90vh] overflow-y-auto">
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
                            <input type="number" name="stock[{{ $key }}]" id="stock_{{ $key }}" placeholder="Enter stock change (use negative to decrease)"
                                    class="ml-4 px-2 py-1 border rounded w-48 hidden">
                        </div>
                    @endforeach
                    </div>
                </div>

                <div class="mb-3">
                    <label class="block text-sm">Image</label>
                    <img id="product-image-preview" class="w-full h-40 object-contain mb-2" src="" alt="">
                    <input type="file" name="image" class="w-full border p-2 rounded">
                </div>

                <div class="mb-3">
                    <label class="block text-sm">Measurement Image</label>
                    <img id="product-measurement-preview" class="w-full h-40 object-contain mb-2" src="" alt="">
                    <input type="file" name="measurement_image" class="w-full border p-2 rounded">
                </div>

                <div class="flex justify-end space-x-2 sticky bottom-0 bg-white pt-2">
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

        function openEditModal(button) {
            const id = button.dataset.id;
            const name = button.dataset.name;
            const desc = button.dataset.desc;
            const price = button.dataset.price;
            const image = button.dataset.image;
            const measurement = button.dataset.measurement;

            // Set form action and values
            const form = document.getElementById('edit-product-form');
            form.action = `/products/${id}`;
            document.getElementById('product-id').value = id;
            document.getElementById('product-name').value = name;
            document.getElementById('product-description').value = desc;
            document.getElementById('product-price').value = price;
            document.getElementById('product-image-preview').src = '/storage/' + image;
            document.getElementById('product-measurement-preview').src = measurement ? ('/storage/' + measurement) : '';

            // Reset sizes: all unchecked, stock = 0, hidden
            Object.keys(@json($sizeNames)).forEach(size => {
                const checkbox = document.getElementById('size_' + size);
                const input = document.getElementById('stock_' + size);

                checkbox.checked = false;
                input.classList.add('hidden');
                input.value = 0;
            });

            // Show modal
            document.getElementById('edit-modal').classList.add('active');
        }


        function openDeleteModal(button) {
            const id = button.dataset.id;
            const form = document.getElementById('delete-form');
            form.action = `/products/delete/${id}`;
            document.getElementById('delete-modal').classList.add('active');
        }

        function closeModal(id) {
            document.getElementById(id).classList.remove('active');
        }
    </script>
</x-layout>

<!-- Real-time Online Counter Script -->
<script>
    (function() {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const countEl = document.getElementById('current-online-count');
        if (!countEl) return;

        async function heartbeat() {
            try {
                await fetch('/analytics/heartbeat', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ ts: Date.now() })
                });
            } catch (e) {
                // silent
            }
        }

        async function refreshCount() {
            try {
                const res = await fetch('/analytics/online-count');
                if (!res.ok) return;
                const data = await res.json();
                if (typeof data.count === 'number') {
                    countEl.textContent = data.count;
                }
            } catch (e) {
                // silent
            }
        }

        // Initial calls
        heartbeat();
        refreshCount();
        // Schedule periodic updates
        setInterval(heartbeat, 30000); // update presence every 30s
        setInterval(refreshCount, 10000); // refresh UI every 10s
    })();
</script>
