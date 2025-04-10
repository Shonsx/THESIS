<x-layout title="Buyer Details">
    <div class="container mx-auto px-4 py-6">
        <h2 class="text-xl font-bold mb-4">Order Details</h2>

        <div class="bg-white p-6 rounded-lg shadow-md max-w-2xl mx-auto">
            <p><strong>Buyer Name:</strong> {{ $order->user ? $order->user->name : 'N/A' }}</p>
            <p><strong>Product:</strong> {{ $order->product ? $order->product->name : 'N/A' }}</p>
            <p><strong>Size:</strong> {{ strtoupper($order->size) }}</p>
            <p><strong>Quantity:</strong> {{ $order->quantity }}</p>
            <p><strong>Total Price:</strong> ₱{{ number_format($order->total_price, 2) }}</p>
            <p><strong>Status:</strong> {{ $order->processed ? 'Processed' : 'Pending' }}</p>
            <p><strong>Ordered At:</strong> {{ $order->created_at->format('F d, Y h:i A') }}</p>

            <a href="{{ route('cashier.main') }}" class="mt-4 inline-block bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                Back to List
            </a>
        </div>
    </div>
</x-layout>
