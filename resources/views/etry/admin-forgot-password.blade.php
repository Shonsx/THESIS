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
                    <div>
                        <label for="username" class="block text-sm font-medium text-black mb-2">Admin Username</label>
                        <input type="text" id="username" name="username" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500" style="font-family: 'Poppins'" />
                    </div>
                    <div>
                        <label for="tel" class="block text-sm font-medium text-black mb-2">Cellphone Number</label>
                        <div class="flex items-center gap-2">
                            <span class="px-3 py-2 bg-gray-200 rounded-md text-black select-none">+63</span>
                            <input type="tel" id="tel" name="tel" placeholder="9XXXXXXXXX" required maxlength="10" class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500" style="font-family: 'Poppins'" />
                        </div>
                        <p class="text-xs text-black mt-1">Starts with 9 and must be 10 digits. We will match it to the admin record.</p>
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
                </form>
                <div class="mt-4 text-center">
                    <a href="{{ route('login') }}" class="text-white hover:text-red-500" style="font-family: 'Poppins'">Back to login</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        const telInput = document.getElementById('tel');
        telInput.addEventListener('input', function() {
            let digits = this.value.replace(/\D/g, '');
            if (digits.length > 10) digits = digits.slice(0, 10);
            // Ensure starts with 9
            if (digits && digits[0] !== '9') digits = '9' + digits.replace(/^\d/, '');
            this.value = digits;
        });
        document.querySelector('form').addEventListener('submit', function(e) {
            const full = '+63' + (telInput.value || '');
            // Inject full E.164 into a hidden clone for server-side matching
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'tel';
            hidden.value = full;
            // Remove current visible input name to avoid duplicate
            telInput.name = '';
            this.appendChild(hidden);
        });
    </script>
</x-layout>