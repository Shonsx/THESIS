<x-layout title="Reset Password">
    <div class="h-screen overflow-hidden flex flex-col">
        <div class="flex-1 flex items-center justify-center bg-cover bg-center" style="background-image: url({{asset('images/BG.png')}})">
            <div class="container max-w-lg bg-[#8c8c8c]/20 backdrop-blur-md rounded-3xl p-8">
                <h2 class="text-3xl text-black font-bold mb-6 text-center" style="font-family: 'Poppins'">Reset Password</h2>
                @if($errors->any())
                    <div class="text-center text-white bg-red-500 px-4 py-2 rounded-md mb-4">
                        {{ $errors->first() }}
                    </div>
                @endif
                <form action="{{ route('password.update') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">

                    <div>
                        <label for="email" class="block text-sm font-medium text-black mb-2">Email address</label>
                        <input type="email" id="email" name="email" value="{{ $email ?? old('email') }}" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500" style="font-family: 'Poppins'" />
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-black mb-2">New Password</label>
                        <input type="password" id="password" name="password" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500" style="font-family: 'Poppins'" />
                        <p class="text-xs text-black mt-1">At least 6 characters, with 1 uppercase letter and 1 number.</p>
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-black mb-2">Confirm Password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500" style="font-family: 'Poppins'" />
                    </div>

                    <button type="submit" class="w-full h-12 bg-white hover:bg-[#FAC000] hover:text-black transition-colors duration-300 text-black text-xl rounded-full" style="font-family: 'Poppins'">Reset Password</button>
                </form>
                <div class="mt-4 text-center">
                    <a href="{{ route('login') }}" class="text-white hover:text-red-500" style="font-family: 'Poppins'">Back to login</a>
                </div>
            </div>
        </div>
    </div>
</x-layout>