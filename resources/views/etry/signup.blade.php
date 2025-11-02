<x-layout title="Register">
    <style>
        .input-box {
            position: relative;
            margin-bottom: 10px; /* reduced spacing */
        }
        .input-box p {
            font-size: 0.875rem;
        }
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
        #password-rule-msg,
        #password-match-msg {
            margin-top: -5px; /* bring them closer to input */
            margin-bottom: 5px;
            font-size: 0.9em;
            line-height: 1.2em;
        }
        .remember-forgot {
            color: white;
            font-size: 1em;
            margin:  0 15px;
            width: 65%;
            display: flex;
            justify-content: space-between;
            font-family: 'Poppins';
        }
        .remember-forgot a:hover {
            text-decoration: underline;
            color: red;
        }
        .remember-forgot input:hover {
            cursor: pointer;
        }
        .remember-forgot a {
            color: white;
            text-decoration: none;
        }
        .remember-forgot input {
            margin-right: 5px; 
        }
        .remember-forgot label {
            margin-left: 5px; 
        }
    </style>

    <div class="h-screen overflow-auto flex flex-col">
        <div class="flex-1 flex items-center justify-center bg-cover bg-center" style="background-image: url({{ asset('images/BG.png') }})">
            <div class="container max-w-lg w-full bg-[#8c8c8c]/20 backdrop-blur-md border-1 rounded-3xl flex flex-col h-auto py-6 px-4">
                
                <div class="w-full flex justify-center text-center mb-6">
                    <h2 class="text-5xl text-black font-bold" style="font-family: 'Poppins'">Register</h2>
                </div>

                <form action="{{ route('register') }}" method="POST" class="flex flex-col items-center text-center gap-3">
                    @csrf

                    <!-- Email -->
                    <div class="relative w-80 input-box">
                        <span class="icon"><ion-icon name="mail"></ion-icon></span>
                        <input type="email" name="email" id="email" placeholder=" " style="font-family: 'Poppins'">
                        <label for="email">Email</label>
                    </div>

                    <!-- Password -->
                    <div class="relative w-80 input-box">
                        <span class="icon"><ion-icon name="lock-closed"></ion-icon></span>
                        <input type="password" name="password" id="password" placeholder=" " style="font-family: 'Poppins'">
                        <label for="password">Password</label>
                    </div>
                    <p id="password-rule-msg" class="text-md text-white"></p>

                    <!-- Confirm Password -->
                    <div class="relative w-80 input-box">
                        <span class="icon"><ion-icon name="lock-closed-outline"></ion-icon></span>
                        <input type="password" name="password_confirmation" id="password_confirmation" placeholder=" " style="font-family: 'Poppins'">
                        <label for="password_confirmation">Re-enter Password</label>
                    </div>
                    <p id="password-match-msg" class="text-md text-white"></p>

                    <!-- Name -->
                    <div class="relative w-80 input-box">
                        <span class="icon"><ion-icon name="person"></ion-icon></span>
                        <input type="text" name="name" id="name" placeholder=" " style="font-family: 'Poppins'">
                        <label for="name">Full Name</label>
                    </div>

                    <!-- Phone -->
                    <div class="relative w-80 input-box">
                        <span class="icon"><ion-icon name="call"></ion-icon></span>
                        <div class="flex items-center space-x-2">
                            <span class="px-3 py-2 bg-gray-200 rounded-md select-none">+63</span>
                            <input type="tel" pattern="[0-9]*" inputmode="numeric" name="tel" id="tel" placeholder="9xxxxxxxxx" style="font-family: 'Poppins'" class="flex-1">
                        </div>
                    </div>

                    <!-- Submit -->
                    <button type="submit" class="w-80 h-12 bg-white hover:bg-red-600 hover:text-white transition-colors duration-300 text-black text-xl rounded-full" style="font-family: 'Poppins'">
                        Register
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Transient Error Modal for Register -->
    @if(session('error') || $errors->any())
    <div id="signupErrorModal" class="fixed inset-0 hidden items-center justify-center z-[60]">
        <div class="absolute inset-0 bg-black/40" onclick="hideModal('signupErrorModal')"></div>
        <div class="relative bg-red-600 text-white rounded-xl shadow-lg px-5 py-4 text-center">
            <p class="text-white font-medium" style="font-family: 'Poppins'">
                @if(session('error'))
                    {{ session('error') }}
                @elseif($errors->has('email'))
                    {{ $errors->first('email') }}
                @else
                    {{ $errors->first() }}
                @endif
            </p>
        </div>
    </div>
    @endif

    <!-- Icons -->
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

    <!-- Validation Scripts -->
    <script>
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('password_confirmation');
        const matchMsg = document.getElementById('password-match-msg');
        const ruleMsg = document.getElementById('password-rule-msg');

        // ✅ Regex: at least 1 uppercase, 1 number, no symbols allowed
        const passwordRegex = /^(?=.*[A-Z])(?=.*\d)[A-Za-z\d]+$/;

        function validatePasswordRules() {
            const value = password.value;

            if (!value) {
                ruleMsg.textContent = "";
                return;
            }

            if (/[^A-Za-z\d]/.test(value)) {
                ruleMsg.textContent = "Symbols are not allowed.";
                ruleMsg.style.color = "red";
            } else if (!/[A-Z]/.test(value)) {
                ruleMsg.textContent = "Must include at least one uppercase letter.";
                ruleMsg.style.color = "red";
            } else if (!/\d/.test(value)) {
                ruleMsg.textContent = "Must include at least one number.";
                ruleMsg.style.color = "red";
            } else if (value.length < 6) {
                ruleMsg.textContent = "Must be at least 6 characters.";
                ruleMsg.style.color = "red";
            } else if (!passwordRegex.test(value)) {
                ruleMsg.textContent = "Invalid password format.";
                ruleMsg.style.color = "red";
            } else {
                ruleMsg.textContent = "Password format looks good!";
                ruleMsg.style.color = "green";
            }
        }

        function validatePasswordsMatch() {
            if (password.value && confirmPassword.value) {
                if (password.value === confirmPassword.value) {
                    matchMsg.textContent = "Passwords match!";
                    matchMsg.style.color = "green";
                } else {
                    matchMsg.textContent = "Passwords do not match.";
                    matchMsg.style.color = "red";
                }
            } else {
                matchMsg.textContent = "";
            }
        }

        password.addEventListener('input', () => {
            validatePasswordRules();
            validatePasswordsMatch();
        });
        confirmPassword.addEventListener('input', validatePasswordsMatch);

        // Phone input handling: enforce 10 digits and +63 prefix
        const telInput = document.getElementById('tel');
        const form = document.querySelector('form[action="{{ route('register') }}"]');
        function sanitizePhone(value) { return (value || '').replace(/\D/g, '').slice(0, 10); }
        function validatePhone() {
            const digits = sanitizePhone(telInput.value);
            telInput.value = digits;
            if (!digits || digits.length !== 10 || !digits.startsWith('9')) {
                telInput.setCustomValidity('Enter 10 digits after +63 starting with 9');
            } else {
                telInput.setCustomValidity('');
            }
        }
        if (telInput) telInput.addEventListener('input', validatePhone);
        if (form) {
            form.addEventListener('submit', function(e) {
                validatePhone();
                const digits = sanitizePhone(telInput.value);
                if (!digits || digits.length !== 10 || !digits.startsWith('9')) { e.preventDefault(); return; }
                telInput.value = '+63' + digits;
            });
        }

        // Transient modal helpers (match login behavior)
        function showTransientModal(id, ms = 3000) {
            const el = document.getElementById(id);
            if (!el) return;
            el.classList.remove('hidden');
            el.classList.add('flex');
            setTimeout(() => hideModal(id), ms);
        }
        function hideModal(id) {
            const el = document.getElementById(id);
            if (!el) return;
            el.classList.add('hidden');
            el.classList.remove('flex');
        }
        document.addEventListener('DOMContentLoaded', () => {
            if (document.getElementById('signupErrorModal')) {
                showTransientModal('signupErrorModal');
            }
        });
    </script>
</x-layout>
