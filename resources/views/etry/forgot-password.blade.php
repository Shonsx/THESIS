<x-layout title="Forgot Password">
    <div class="h-screen overflow-hidden flex flex-col">
        <div class="flex-1 flex items-center justify-center bg-cover bg-center" style="background-image: url({{asset('images/BG.png')}})">
            <div class="container max-w-lg bg-[#8c8c8c]/20 backdrop-blur-md rounded-3xl p-8">
                <h2 class="text-3xl text-black font-bold mb-6 text-center" style="font-family: 'Poppins'">Forgot Password</h2>
                @if(session('status'))
                    <div class="text-center text-white bg-green-600 px-4 py-2 rounded-md mb-4">
                        {{ session('status') }}
                    </div>
                @endif
                @if($errors->any())
                    <div class="text-center text-white bg-red-500 px-4 py-2 rounded-md mb-4">
                        {{ $errors->first() }}
                    </div>
                @endif
                <form action="{{ route('password.email') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="email" class="block text-sm font-medium text-black mb-2">Email address</label>
                        <input type="email" id="email" name="email" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500" style="font-family: 'Poppins'" />
                    </div>
                    <button type="submit" class="w-full h-12 bg-white hover:bg-[#FAC000] hover:text-black transition-colors duration-300 text-black text-xl rounded-full" style="font-family: 'Poppins'">Send Password Reset Link</button>
                </form>
                <div class="mt-4 text-center">
                    <a href="{{ route('login') }}" class="text-white hover:text-red-500" style="font-family: 'Poppins'">Back to login</a>
                </div>
            </div>
        </div>
    </div>
</x-layout>