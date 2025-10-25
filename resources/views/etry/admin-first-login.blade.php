<x-layout title="Admin Setup">
    <style>
        .input-box input {
            width: 100%;
            height: 50px;
            background: white;
            border: 2px solid #2c4766;
            outline: none;
            border-radius: 40px;
            font-size: 1em;
            color: black;
            padding: 0 20px 0 20px;
            padding-right: 50px;
            transition: .5s ease;
        }
        .input-box .icon {
            position: absolute;
            right: 15px;
            color: black;
            font-size: 1.7em;
            line-height: 55px;
        }
        .input-box input:focus {
            border-color: black;
        }
        .input-box label {
            position: absolute;
            top: 50%;
            left: 20px;
            transform: translateY(-50%);
            font-size: 1.1em;
            color: black;
            pointer-events: none;
            transition: .5s ease;
        }
        .input-box input:focus~label,
        .input-box input:not(:placeholder-shown)~label {
            top: 1px;
            font-size: 1em;
            background: #000000;
            padding: 0 6px;
            color: white;
        }
    </style>

    <div class="h-screen overflow-hidden flex flex-col">
        <div class="flex-1 flex items-center justify-center bg-cover bg-center" style="background-image: url({{asset('images/BG.png')}})">
            <div class="container max-w-lg bg-[#8c8c8c]/20 backdrop-blur-md border-1 rounded-3xl flex flex-col p-8">
                <div class="container w-full flex justify-center items-center text-center mb-6">
                    <h2 class="text-4xl text-black font-bold" style="font-family: 'Poppins'">Admin Setup Required</h2>
                </div>
                
                <div class="text-center mb-6">
                    <p class="text-black text-lg" style="font-family: 'Poppins'">
                        Welcome! This is your first login as admin. Please set up your account by changing your password and adding a cellphone number.
                    </p>
                </div>

                <form action="{{ route('admin.update-profile') }}" method="POST" class="flex flex-col items-center text-center">
                    @csrf
                    
                    <div class="relative my-2 w-80 input-box">
                        <span class="icon"><ion-icon name="call"></ion-icon></span>
                        <div class="flex items-center space-x-2">
                            <span class="px-3 py-2 bg-gray-200 rounded-md select-none">+63</span>
                            <input type="tel" name="tel" id="tel" placeholder="9xxxxxxxxx" required pattern="[0-9]*" inputmode="numeric" style="font-family: 'Poppins'" class="flex-1">
                        </div>
                    </div>
                    @error('tel')
                        <div class="text-red-500 text-sm mb-2">{{ $message }}</div>
                    @enderror

                    <div class="relative my-2 w-80 input-box">
                        <span class="icon"><ion-icon name="lock-closed"></ion-icon></span>
                        <input type="password" name="password" id="password" placeholder=" " required style="font-family: 'Poppins'">
                        <label for="password">New Password</label>
                    </div>
                    @error('password')
                        <div class="text-red-500 text-sm mb-2">{{ $message }}</div>
                    @enderror

                    <div class="relative my-2 w-80 input-box">
                        <span class="icon"><ion-icon name="lock-closed"></ion-icon></span>
                        <input type="password" name="password_confirmation" id="password_confirmation" placeholder=" " required style="font-family: 'Poppins'">
                        <label for="password_confirmation">Confirm Password</label>
                    </div>

                    <div class="text-white text-sm mb-4 w-80" style="font-family: 'Poppins'">
                        <p>Password requirements:</p>
                        <ul class="list-disc list-inside text-left">
                            <li>At least 6 characters</li>
                            <li>At least 1 uppercase letter</li>
                            <li>At least 1 number</li>
                            <li>No symbols allowed</li>
                        </ul>
                    </div>

                    @if(session('error'))
                        <div class="text-center text-white bg-red-500 px-4 py-2 rounded-md mb-4">
                            {{ session('error') }}
                        </div>
                    @endif

                    <button type="submit" class="w-80 h-12 bg-red-600 hover:bg-red-700 transition-colors duration-300 text-white text-xl rounded-full my-4" style="font-family: 'Poppins'">
                        Complete Setup
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

    <!-- Password and phone validation script -->
    <script>
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('password_confirmation');
        const telInput = document.getElementById('tel');
        const form = document.querySelector('form[action="{{ route('admin.update-profile') }}"]');

        // Password validation regex: at least 1 uppercase, 1 number, no symbols allowed
        const passwordRegex = /^(?=.*[A-Z])(?=.*\d)[A-Za-z\d]+$/;

        function validatePassword() {
            const value = password.value;
            if (value && !passwordRegex.test(value)) {
                password.setCustomValidity('Password must contain at least 1 uppercase letter, 1 number, and no symbols');
            } else {
                password.setCustomValidity('');
            }
        }

        function validatePasswordsMatch() {
            if (password.value !== confirmPassword.value) {
                confirmPassword.setCustomValidity('Passwords do not match');
            } else {
                confirmPassword.setCustomValidity('');
            }
        }

        function sanitizePhone(value) {
            return (value || '').replace(/\D/g, '').slice(0, 10);
        }

        function validatePhone() {
            const digits = sanitizePhone(telInput.value);
            telInput.value = digits;
            if (!digits || digits.length !== 10 || !digits.startsWith('9')) {
                telInput.setCustomValidity('Enter 10 digits after +63 starting with 9');
            } else {
                telInput.setCustomValidity('');
            }
        }

        password.addEventListener('input', validatePassword);
        confirmPassword.addEventListener('input', validatePasswordsMatch);
        if (telInput) telInput.addEventListener('input', validatePhone);

        if (form) {
            form.addEventListener('submit', function(e) {
                validatePhone();
                const digits = sanitizePhone(telInput.value);
                if (!digits || digits.length !== 10 || !digits.startsWith('9')) {
                    e.preventDefault();
                    return;
                }
                telInput.value = '+63' + digits;
            });
        }
    </script>
</x-layout>