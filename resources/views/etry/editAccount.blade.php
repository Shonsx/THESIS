<x-layout title="User Update">
    <div class="flex items-center justify-center min-h-screen bg-gray-200">
        <div class="bg-white shadow-md rounded-2xl p-8 w-full max-w-md">
            <h1 class="text-center text-2xl font-bold mb-6">EDIT ACCOUNT</h1>

            <form action="{{ route('updateAccount') }}" method="POST">
                @csrf

                <div class="mb-4">
                    <p class="text-sm text-gray-600">Email</p>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" 
                           class="w-full border rounded-xl p-2" required>
                    <hr class="my-2">
                </div>

                @if (!$user->is_admin)
                <div class="mb-4">
                    <p class="text-sm text-gray-600">Name</p>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" 
                           class="w-full border rounded-xl p-2" required>
                    <hr class="my-2">
                </div>
                @endif

                <div class="mb-4">
                    <p class="text-sm text-gray-600">Mobile Number</p>
                    <input type="tel" name="tel" value="{{ old('tel', $user->tel) }}" 
                           class="w-full border rounded-xl p-2" required>
                    <hr class="my-2">
                </div>

                <div class="mb-4">
                    <p class="text-sm text-gray-600">New Password (Optional)</p>
                    <input type="password" name="password" class="w-full border rounded-xl p-2">
                    <hr class="my-2">
                </div>

                <div class="mb-4">
                    <p class="text-sm text-gray-600">Confirm New Password</p>
                    <input type="password" name="password_confirmation" 
                           class="w-full border rounded-xl p-2">
                           @error('password')
                                <p class="text-red-500 text-sm">{{ $message }}</p>
                            @enderror
                    <hr class="my-2">
                </div>
                

                <div class="flex justify-center space-x-4">
                    <button type="submit" 
                            class="bg-red-700 text-white px-4 py-2 rounded-xl hover:bg-red-800">
                        Update Account
                    </button>

                    <a href="{{ route('account') }}" 
                       class="bg-gray-500 text-white px-4 py-2 rounded-xl hover:bg-gray-600">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-layout>
