<x-layout title="Account Settings">
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Account Settings</h1>

        <!-- Success message -->
        @if(session('success'))
            <div class="bg-green-500 text-white p-4 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <!-- User's Address -->
        <div class="bg-white rounded-lg shadow-lg p-4 mb-4">
            <h2 class="text-lg font-bold mb-2">Shipping Address</h2>

            <!-- Check if the user has an address -->
            @php
                $address = auth()->user()->address;
            @endphp
            @if($address && $address->exists)
                <p><strong>Address:</strong> {{ $address->address }}</p>
                <p><strong>City:</strong> {{ $address->city }}</p>
                <p><strong>State/Province:</strong> {{ $address->state }}</p>
                <p><strong>Zip/Postal Code:</strong> {{ $address->zip }}</p>
            @else
                <p>No address set yet.</p>
            @endif

            <!-- Button to open the Edit Address Modal -->
            <a href="javascript:void(0);" class="text-blue-500" onclick="toggleEditAddressForm()">Edit Address</a>
        </div>

        <!-- Edit Address Form (Modal) -->
        <div id="editAddressModal" class="hidden fixed top-0 left-0 w-full h-full bg-opacity-50 items-center justify-center">
            <div class="bg-white p-6 rounded-2xl shadow-lg w-96 border-2 border-gray-800">
                <h2 class="text-lg font-bold mb-2">Edit Shipping Address</h2>
                <form action="{{ route('account.updateAddress') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="address" class="font-semibold block">Address:</label>
                        <input type="text" name="address" id="address" value="{{ old('address', $address->address ?? '') }}" required class="border px-2 py-1 rounded w-full">
                    </div>
                    <div class="mb-4">
                        <label for="city" class="font-semibold block">City:</label>
                        <input type="text" name="city" id="city" value="{{ old('city', $address->city ?? '') }}" required class="border px-2 py-1 rounded w-full">
                    </div>
                    <div class="mb-4">
                        <label for="state" class="font-semibold block">State/Province:</label>
                        <input type="text" name="state" id="state" value="{{ old('state', $address->state ?? '') }}" required class="border px-2 py-1 rounded w-full">
                    </div>
                    <div class="mb-4">
                        <label for="zip" class="font-semibold block">Zip/Postal Code:</label>
                        <input type="text" name="zip" id="zip" value="{{ old('zip', $address->zip ?? '') }}" required class="border px-2 py-1 rounded w-full">
                    </div>

                    <!-- Buttons aligned side-by-side -->
                    <div class="flex justify-between">
                        <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-600">Save Address</button>
                        <button type="button" onclick="toggleEditAddressForm()" class="bg-red-500 text-white px-6 py-2 rounded-lg hover:bg-red-600">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <!-- JavaScript to toggle the modal visibility -->
    <script>
        function toggleEditAddressForm() {
            const modal = document.getElementById('editAddressModal');
            modal.classList.toggle('hidden');
            modal.classList.toggle('flex');
        }
    </script>
</x-layout>
