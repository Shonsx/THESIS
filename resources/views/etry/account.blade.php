<x-layout title="Account">
    <div class="flex items-center justify-center min-h-screen bg-gray-200">
        <div class="bg-white shadow-md rounded-2xl p-8 w-full max-w-md">
            <h1 class="text-center text-2xl font-bold mb-6">USER ACCOUNT</h1>

            <div class="mb-4">
                <p class="text-sm text-gray-600">Username</p>
                <p class="font-semibold">{{ $user->name }}</p>
                <hr class="my-2">
            </div>

            <div class="mb-4">
                <p class="text-sm text-gray-600">Email</p>
                <p class="font-semibold">{{ $user->email }}</p>
                <hr class="my-2">
            </div>

            <div class="mb-4">
                <p class="text-sm text-gray-600">Password</p>
                <hr class="my-2">
            </div>

            <div class="mb-4">
                <p class="text-sm text-gray-600">Mobile Number</p>
                <p class="font-semibold">{{ substr($user->tel, 0, 2) . str_repeat('*', 7) . substr($user->tel, -2) }}</p>
                <hr class="my-2">
            </div>

            <div class="mb-6">
                <p class="text-sm text-gray-600">Created At</p>
                <p class="font-semibold">{{ $user->created_at->format('l, F jS Y') }}</p>
                <hr class="my-2">
            </div>

            <div class="flex justify-center space-x-4">
                <a href="{{ route('editAccount') }}" 
                   class="bg-red-700 text-white px-4 py-2 rounded-xl hover:bg-red-800">Update</a>

                @if ($user && $user->name !== 'ADMIN')
                    <button onclick="openDeleteModal()" class="bg-red-700 text-white px-4 py-2 rounded-xl hover:bg-red-800">Delete</button>
                @endif
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 backdrop-blur-md bg-opacity-50 items-center justify-center hidden">
        <div class="bg-white p-6 rounded-lg w-96 text-center border-2 border-gray-800 shadow-lg mx-auto mt-20">
            <h3 class="text-xl font-bold mb-4">Are you sure you want to delete your account?</h3>
            <p class="mb-6 text-gray-600">This action cannot be undone.</p>
            <div class="flex justify-between">
                <!-- Cancel Button -->
                <button onclick="closeDeleteModal()" class="bg-gray-500 text-white px-4 py-2 rounded-xl hover:bg-gray-600">Cancel</button>

                <!-- Confirm Delete Form -->
                <form action="{{ route('delete-account') }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-700 text-white px-4 py-2 rounded-xl hover:bg-red-800">Delete Account</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openDeleteModal() {
            const modal = document.getElementById('deleteModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeDeleteModal() {
            const modal = document.getElementById('deleteModal');
            modal.classList.add('hidden');
        }
    </script>
</x-layout>
