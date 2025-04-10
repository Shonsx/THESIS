<x-layout title="Order History">
    <div class="container mx-auto px-4 py-6">
        <h2 class="text-xl font-bold mb-4">Order History</h2>

        @if(session('success'))
            <p class="text-green-600 mb-4">{{ session('success') }}</p>
        @endif

        <div class="overflow-x-auto bg-white p-4 rounded-lg shadow-md mx-auto max-w-4xl">
            <table class="table-auto w-full border-collapse border border-gray-300">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="border px-4 py-2 text-center">Username</th>
                        <th class="border px-4 py-2 text-center">Product Name</th>
                        <th class="border px-4 py-2 text-center">Size</th>
                        <th class="border px-4 py-2 text-center">Quantity</th>
                        <th class="border px-4 py-2 text-center">Total Price</th>
                        <th class="border px-4 py-2 text-center">Completed At</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($processedOrders as $order)
                        <tr>
                            <td class="border px-4 py-2 text-center">{{ $order->user ? $order->user->name : 'No user' }}</td>
                            <td class="border px-4 py-2 text-center">{{ $order->product ? $order->product->name : 'No product' }}</td>
                            <td class="border px-4 py-2 text-center">{{ strtoupper($order->size) }}</td>
                            <td class="border px-4 py-2 text-center">{{ $order->quantity }}</td>
                            <td class="border px-4 py-2 text-center">₱{{ number_format($order->total_price, 2) }}</td>
                            <td class="border px-4 py-2 text-center">{{ $order->updated_at->format('F d, Y h:i A') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($processedOrders->isEmpty())
            <p class="text-gray-500 text-center mt-4">No completed orders.</p>
        @endif
    </div>
</x-layout>
