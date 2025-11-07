<x-layout title="Add Product">
    <div class="container mx-auto p-4">
        @if(session('error'))
            <div class="mb-4 bg-red-100 text-red-700 px-4 py-2 rounded">
                {{ session('error') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="mb-4 bg-red-100 text-red-700 px-4 py-2 rounded">
                <ul class="list-disc ml-6">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <h1 class="text-2xl font-bold text-center">Add Product</h1>
        <form action="{{ route('addProduct.store') }}" method="POST" class="w-1/2 mx-auto mt-4" enctype="multipart/form-data">
            @csrf

            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                <input type="text" name="name" id="name" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500" required>
            </div>
            <div class="mb-4">
                <label for="gender" class="block font-semibold">Gender:</label>
                <div class="flex items-center space-x-4">
                    <label class="flex items-center space-x-2">
                        <input type="radio" name="gender" value="Men" 
                            {{ old('gender') == 'Men' ? 'checked' : '' }} required>
                        <span>Men</span>
                    </label>

                    <label class="flex items-center space-x-2">
                        <input type="radio" name="gender" value="Women" 
                            {{ old('gender') == 'Women' ? 'checked' : '' }} required>
                        <span>Women</span>
                    </label>
                </div>
            </div>            

            <div class="mb-4">
                <label for="measurement_image" class="block text-sm font-medium text-gray-700">Measurement Image</label>
                <input type="file" name="measurement_image" id="measurement_image" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 cursor-pointer hover:border-blue-500" accept="image/*">
            </div>


            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Sizes & Stock</label>
                <div id="sizes-wrapper" class="space-y-2">
                    @foreach(['S', 'M', 'L', 'XL'] as $size)
                        <div class="flex items-center space-x-2">
                            <input type="checkbox" id="size_{{ $size }}" name="sizes[]" value="{{ $size }}" onchange="toggleStockInput('{{ $size }}')">
                            <label for="size_{{ $size }}" class="flex-grow">{{ $size }}</label>
                            <input type="number" name="stock[{{ $size }}]" id="stock_{{ $size }}" placeholder="Stock for {{ $size }}"
                                class="ml-4 px-2 py-1 border rounded w-32 hidden" min="0">
                        </div>
                    @endforeach
                </div>
            </div>          
            <div class="mb-4">
                <label for="price" class="block text-sm font-medium text-gray-700">Price</label>
                <input type="number" name="price" id="price" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500" required>
            </div>
            <div class="mb-4">
                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                <textarea name="description" id="description" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500" required></textarea>
            </div>
            <div class="mb-4">
                <label for="image" class="block text-sm font-medium text-gray-700">Display Product Image</label>
                <input type="file" name="image" id="image" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 cursor-pointer hover:border-blue-500" accept="image/*">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">More Product images</label>
                <p class="text-xs text-gray-500 mb-2">You can upload up to 6 images.</p>
                <div id="extraImagesContainer" class="space-y-2">
                    <input type="file" name="extra_images[]" accept="image/*" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 cursor-pointer hover:border-blue-500">
                </div>
                <div class="flex items-center justify-between mt-2">
                    <button type="button" id="addExtraImageBtn" class="px-3 py-1 bg-gray-800 text-white rounded hover:bg-[#00c7c7] transition">Add another image</button>
                    <span class="text-xs text-gray-600">Selected: <span id="extraImagesCount">0</span>/6</span>
                </div>
            </div>
            <button type="submit" class="w-full px-3 py-2 bg-[#B22222] text-white rounded-md hover:bg-[#00c7c7] transition duration-500">Add Product</button>
        </form>
    </div>

    <script>
    function toggleStockInput(size) {
        const checkbox = document.getElementById(`size_${size}`);
        const stockInput = document.getElementById(`stock_${size}`);
        stockInput.classList.toggle('hidden', !checkbox.checked);
    }
    function toggleCustomUpload(show) {
        const input = document.getElementById('customMeasurementInput');
        input.classList.toggle('hidden', !show);
    }

    // Also hide input if other radio is selected
    document.querySelectorAll('input[name="measurement_type"]').forEach(radio => {
        radio.addEventListener('change', (e) => {
            if (e.target.value !== 'custom') {
                toggleCustomUpload(false);
            }
        });
    });

</script>
<script>
    (function() {
        const maxExtra = 6;
        const container = document.getElementById('extraImagesContainer');
        const addBtn = document.getElementById('addExtraImageBtn');
        const countEl = document.getElementById('extraImagesCount');

        function updateCount() {
            let total = 0;
            container.querySelectorAll('input[type="file"]').forEach(inp => {
                total += (inp.files ? inp.files.length : 0);
            });
            countEl.textContent = total;
            addBtn.disabled = total >= maxExtra || container.querySelectorAll('input[type="file"]').length >= maxExtra;
        }

        function addExtraInput() {
            // Prevent exceeding max number of inputs
            if (container.querySelectorAll('input[type="file"]').length >= maxExtra) return;
            const input = document.createElement('input');
            input.type = 'file';
            input.name = 'extra_images[]';
            input.accept = 'image/*';
            input.className = 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 cursor-pointer hover:border-blue-500';
            input.addEventListener('change', updateCount);
            container.appendChild(input);
        }

        // Hook up initial input
        const initial = container.querySelector('input[type="file"]');
        if (initial) initial.addEventListener('change', updateCount);

        addBtn.addEventListener('click', () => {
            addExtraInput();
        });

        // Initial state
        updateCount();
    })();
</script>
</x-layout>
