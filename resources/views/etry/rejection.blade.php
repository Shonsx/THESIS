<x-layout title="Order Rejection">
    <style>
        body { position: relative; min-height: 100vh; margin: 0; }
        body::before {
            content: ""; position: absolute; top: 0; left: 0; right: 0; bottom: 0;
            background-image: url('{{ asset('images/BG-1.jpg') }}'); background-size: cover; background-position: center; background-repeat: no-repeat; opacity: 0.3; z-index: -1;
        }
    </style>
    <div class="container mx-auto px-4 py-6">
        <div class="bg-white p-4 md:p-6 rounded-lg shadow-md max-w-3xl mx-auto">
            <h2 class="text-xl md:text-2xl font-bold mb-4 text-center">Order Details</h2>
            <p><strong>Product:</strong> {{ $order->product ? $order->product->name : 'N/A' }}</p>
            <p><strong>Size:</strong> {{ strtoupper($order->size) }}</p>
            <p><strong>Quantity:</strong> {{ $order->quantity }}</p>
            <p><strong>Total Price:</strong> ₱{{ number_format($order->total_price, 2) }}</p>
            <p><strong>Status:</strong> Rejected</p>
            <p class="mt-4"><strong>Reason:</strong></p>
            <div class="border rounded p-3 bg-gray-50">
                @if (!empty($reason))
                    <p>{{ $reason }}</p>
                @else
                    <p>
                        Please check your payment details. If proof of payment is missing or incorrect, re-upload a valid image and ensure all information is correct.
                    </p>
                @endif
            </div>
            {{-- Do not show proof of payment image per requirement --}}
            <div class="mt-6">
                @if (empty($order->payment_proof_path))
                <form action="{{ route('orders.resubmit', $order->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4 mt-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium">Re-upload Payment Proof</label>
                        <input type="file" name="payment_proof" accept="image/*" class="mt-1 block w-full border rounded p-2" required>
                    </div>
                    <button type="submit" class="w-full md:w-auto px-4 py-2 bg-blue-600 text-white rounded">Resubmit for processing</button>
                </form>
                @else
                    <div class="mt-4 p-3 bg-green-50 border border-green-200 rounded">
                        <p class="text-green-700">Your resubmission is pending review.</p>
                    </div>
                @endif
            </div>
            <div class="mt-6">
                <a href="{{ route('products.index') }}" class="inline-block bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Back to Products</a>
            </div>
        </div>
    </div>
</x-layout>