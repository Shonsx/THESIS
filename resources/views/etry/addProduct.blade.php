<x-layout title="Add Product">
    <div class="container mx-auto p-4">
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
                <label for="image" class="block text-sm font-medium text-gray-700">Image</label>
                <input type="file" name="image" id="image" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 cursor-pointer hover:border-blue-500" required>
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
</script>
</x-layout>
