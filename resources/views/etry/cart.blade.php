<x-layout title="Cart List">
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">🛒 Your Shopping Cart</h1>

        @if(count($cartItems) > 0)
            <!-- Bulk Action Form -->
            <form id="cart-form" method="POST" action="{{ route('cart.bulkAction') }}">
                @csrf

                <div class="flex items-center mb-4">
                    <input type="checkbox" id="select-all" class="mr-2 w-5 h-5">
                    <label for="select-all" class="text-lg font-semibold cursor-pointer">Select All</label>
                </div>

                <div class="grid grid-cols-1 gap-4">
                    @foreach($cartItems as $item)
                        <div class="bg-white rounded-lg shadow-lg p-4 flex items-center">
                            <input type="checkbox" name="selected_items[]" value="{{ $item->id }}" 
                                   class="cart-checkbox mr-3 w-5 h-5">

                            <img src="{{ asset('storage/' . $item->product->image) }}" 
                                 alt="{{ $item->product->name }}" 
                                 class="w-24 h-24 object-cover rounded">

                            <div class="ml-4 flex-grow">
                                <h2 class="text-lg font-bold">{{ $item->product->name }}</h2>
                                <p class="text-gray-600">{{ $item->product->description }}</p>
                                <p class="text-lg font-semibold">₱{{ $item->product->price }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Bulk Actions -->
                <div class="flex space-x-4 mt-6">
                    <button type="submit" name="action" value="buy"
                        class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">
                        Buy Selected
                    </button>

                    <button type="submit" name="action" value="remove"
                        class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600">
                        Remove Selected
                    </button>
                </div>
            </form>
        @else
            <p class="text-gray-500">Your cart is empty.</p>
        @endif
    </div>

    <script>
        document.getElementById('select-all').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.cart-checkbox');
            checkboxes.forEach(checkbox => checkbox.checked = this.checked);
        });
    </script>
</x-layout>
