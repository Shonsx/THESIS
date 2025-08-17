<x-layout title="Buyer Details">
    <style>
        body {
            position: relative;
            min-height: 100vh;
            margin: 0;
        }

        body::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url('{{ asset('images/BG-1.jpg') }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            opacity: 0.3; 
            z-index: -1;
        }
    </style>
    <div class="container mx-auto px-4 py-6">

        <div class="bg-white p-6 rounded-lg shadow-md max-w-2xl mx-auto">
            <h2 class="text-2xl font-bold mb-4 text-center">Order Details</h2>
            <p><strong>Buyer Name:</strong> {{ $order->user ? $order->user->name : 'N/A' }}</p>
            <p><strong>Product:</strong> {{ $order->product ? $order->product->name : 'N/A' }}</p>
            <p><strong>Size:</strong> {{ strtoupper($order->size) }}</p>
            <p><strong>Quantity:</strong> {{ $order->quantity }}</p>
            <p><strong>Shipping Address:</strong> 
                {{ $order->user && $order->user->address 
                    ? "{$order->user->address->address}, {$order->user->address->city}, {$order->user->address->state}, {$order->user->address->zip}" 
                    : 'N/A' }}
            </p>
            <p><strong>Total Price:</strong> ₱{{ number_format($order->total_price, 2) }}</p>
            <p><strong>Status:</strong> {{ $order->processed ? 'Processed' : 'Pending' }}</p>
            <p><strong>Ordered At:</strong> {{ $order->created_at->format('F d, Y h:i A') }}</p>

            @if ($order->payment_proof_path)
                <p><strong>Proof of Payment:</strong></p>
                <img src="{{ asset('storage/' . $order->payment_proof_path) }}" alt="Proof of Payment" class="mt-2 rounded border shadow-md w-full max-w-xs">
            @else
                <p><strong>Proof of Payment:</strong> Not uploaded</p>
            @endif

            <a href="{{ route('cashier.main') }}" class="mt-4 inline-block bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                Back to List
            </a>

            @if (!$order->processed)
                <form action="{{ route('cashier.updateStatus', $order->id) }}" method="POST" class="inline-block">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="mt-4 bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                        Mark as Processed
                    </button>
                </form>
            @endif
        </div>
    </div>
</x-layout>
