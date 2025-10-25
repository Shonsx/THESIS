<x-layout title="Cart List">
    <div class="container mx-auto p-4">
        <div class="flex flex-col">
            <!-- Shopping Cart (Top Half) -->
            <div class="w-full p-4 mb-6">
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

                    <!-- Cart Pagination -->
                    <div class="mt-4">
                        {{ $cartItems->appends(['cart_page' => $cartItems->currentPage()])->links() }}
                    </div>

                @else
                    <p class="text-gray-500">Your cart is empty.</p>
                @endif
            </div>

            <!-- Order History (Bottom Half) -->
            <div class="w-full p-4">
                <h2 class="text-xl font-bold mb-4 border-b pb-2">🧾 Your Order History</h2>

                @if($userOrders && $userOrders->count() > 0)
                    <div class="order-history">
                        <table class="table-auto w-full border-collapse border border-gray-300">
                            <thead>
                                <tr class="bg-gray-200">
                                    <th class="border px-4 py-2 text-center">Product Image</th> <!-- Changed header -->
                                    <th class="border px-4 py-2 text-center">Product Name</th>
                                    <th class="border px-4 py-2 text-center">Size</th>
                                    <th class="border px-4 py-2 text-center">Quantity</th>
                                    <th class="border px-4 py-2 text-center">Total Price</th>
                                    <th class="border px-4 py-2 text-center">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($userOrders as $order)
                                    <tr>
                                        <td class="border px-4 py-2 text-center">
                                            @if($order->product && $order->product->image)
                                                <img src="{{ asset('storage/' . $order->product->image) }}" 
                                                    alt="{{ $order->product->name }}" 
                                                    class="mx-auto w-16 h-16 object-cover rounded" />
                                            @else
                                                <span>No image</span>
                                            @endif
                                        </td>

                                        <td class="border px-4 py-2 text-center">{{ $order->product->name ?? 'No product' }}</td>
                                        
                                        <td class="border px-4 py-2 text-center">{{ $order->size ?? 'N/A' }}</td>

                                        <td class="border px-4 py-2 text-center">{{ $order->quantity }}</td>
                                        <td class="border px-4 py-2 text-center">₱{{ number_format($order->total_price, 2) }}</td>
                                        <td class="border px-4 py-2 text-center">{{ $order->updated_at->setTimezone('Asia/Manila')->format('F d, Y h:i A') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <!-- Order History Pagination -->
                        <div class="mt-4">
                            {{ $userOrders->appends(['order_page' => $userOrders->currentPage()])->links() }}
                        </div>
                    </div>
                @else
                    <p class="text-gray-500">No orders found.</p>
                @endif
            </div>

        </div>
    </div>

    <script>
        document.getElementById('select-all').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.cart-checkbox');
            checkboxes.forEach(checkbox => checkbox.checked = this.checked);
        });
    </script>
</x-layout>
