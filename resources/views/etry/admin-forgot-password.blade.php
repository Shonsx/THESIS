<x-layout title="Admin Forgot Password">
    <div class="h-screen overflow-hidden flex flex-col">
        <div class="flex-1 flex items-center justify-center bg-cover bg-center" style="background-image: url({{asset('images/BG.png')}})">
            <div class="container max-w-lg bg-[#8c8c8c]/20 backdrop-blur-md rounded-3xl p-8">
                <h2 class="text-3xl text-black font-bold mb-6 text-center" style="font-family: 'Poppins'">Admin Forgot Password</h2>
                @if(session('success'))
                    <div class="text-center text-white bg-green-600 px-4 py-2 rounded-md mb-4">
                        {{ session('success') }}
                    </div>
                @endif
                @if($errors->any())
                    <div class="text-center text-white bg-red-500 px-4 py-2 rounded-md mb-4">
                        {{ $errors->first() }}
                    </div>
                @endif
                <form action="{{ route('admin.password.update') }}" method="POST" class="space-y-4">
                    @csrf
                    @if(!session('code_sent'))
                        <input type="hidden" name="step" value="send" />
                        <div>
                            <label for="username" class="block text-sm font-medium text-black mb-2">Admin Username</label>
                            <input type="text" id="username" name="username" value="{{ old('username') }}" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500" style="font-family: 'Poppins'" />
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-medium text-black mb-2">Admin Email</label>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500" style="font-family: 'Poppins'" />
                            <p class="text-xs text-black mt-1">We will send a 6-digit verification code to this email.</p>
                        </div>
                        <button type="submit" class="w-full h-12 bg-red-600 hover:bg-red-700 text-white transition-colors duration-300 text-xl rounded-full" style="font-family: 'Poppins'">Send Verification Code</button>
                    @else
                        <input type="hidden" name="step" value="verify" />
                        <input type="hidden" name="email" value="{{ old('email') }}" />
                        <div>
                            <label class="block text-sm font-medium text-black mb-2">Admin Email</label>
                            <div class="w-full px-3 py-2 border border-gray-200 rounded-md bg-gray-100 text-black select-none" style="font-family: 'Poppins'">{{ old('email') }}</div>
                        </div>
                        <div>
                            <label for="code" class="block text-sm font-medium text-black mb-2">Verification Code</label>
                            <input type="text" id="code" name="code" inputmode="numeric" pattern="\d{6}" maxlength="6" placeholder="6-digit code" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500" style="font-family: 'Poppins'" />
                            <p class="text-xs text-black mt-1">Enter the 6-digit code sent to your email. Expires in 10 minutes.</p>
                        </div>
                        <div>
                            <label for="password" class="block text-sm font-medium text-black mb-2">New Password</label>
                            <input type="password" id="password" name="password" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500" style="font-family: 'Poppins'" />
                            <p class="text-xs text-black mt-1">At least 6 characters, with 1 uppercase letter and 1 number.</p>
                        </div>
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-black mb-2">Confirm Password</label>
                            <input type="password" id="password_confirmation" name="password_confirmation" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500" style="font-family: 'Poppins'" />
                        </div>
                        <button type="submit" class="w-full h-12 bg-red-600 hover:bg-red-700 text-white transition-colors duration-300 text-xl rounded-full" style="font-family: 'Poppins'">Reset Admin Password</button>
                    @endif
                </form>
                <div class="mt-4 text-center">
                    <a href="{{ route('login') }}" class="text-white hover:text-red-500" style="font-family: 'Poppins'">Back to login</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Restrict code input to 6 digits
        const codeInput = document.getElementById('code');
        if (codeInput) {
            codeInput.addEventListener('input', function() {
                let digits = this.value.replace(/\D/g, '');
                this.value = digits.slice(0, 6);
            });
        }
    </script>
</x-layout>