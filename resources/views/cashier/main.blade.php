<x-layout title="Cashier Panel">
    <div class="container mx-auto px-4 py-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold">Orders List</h2>
            <a href="{{ route('cashier.history') }}" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                History
            </a>
        </div>

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
                        <th class="border px-4 py-2 text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orders as $order)
                        <tr>
                            <td class="border px-4 py-2 text-center">{{ $order->user->name ?? 'No user' }}</td>
                            <td class="border px-4 py-2 text-center">{{ $order->product->name ?? 'No product' }}</td>
                            <td class="border px-4 py-2 text-center">{{ strtoupper($order->size) }}</td>
                            <td class="border px-4 py-2 text-center">{{ $order->quantity }}</td>
                            <td class="border px-4 py-2 text-center">₱{{ number_format($order->total_price, 2) }}</td>
                            <td class="border px-4 py-2 text-center">
                                <a href="{{ route('cashier.orderDetails', $order->id) }}" class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600">
                                    Click Details
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($orders->isEmpty())
            <p class="text-gray-500 text-center mt-4">No pending orders.</p>
        @endif
    </div>
</x-layout>
