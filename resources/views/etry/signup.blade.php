<x-layout title="Register">
    <style>
        .input-box {
            position: relative;
            margin-bottom: 15px;
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

                <form action="{{ route('register') }}" method="POST" class="flex flex-col items-center text-center gap-4">
                    @csrf

                    <div class="relative w-80 input-box">
                        <span class="icon"><ion-icon name="mail"></ion-icon></span>
                        <input type="email" name="email" id="email" placeholder=" " style="font-family: 'Poppins'">
                        <label for="email">Email</label>
                        @error('email')
                            <p class="absolute text-xs text-white -bottom-5 left-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="relative w-80 input-box">
                        <span class="icon"><ion-icon name="lock-closed"></ion-icon></span>
                        <input type="password" name="password" id="password" placeholder=" " style="font-family: 'Poppins'">
                        <label for="password">Password</label>
                        @error('password')
                            <p class="absolute text-xs text-white -bottom-5 left-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="relative w-80 input-box">
                        <span class="icon"><ion-icon name="lock-closed-outline"></ion-icon></span>
                        <input type="password" name="password_confirmation" id="password_confirmation" placeholder=" " style="font-family: 'Poppins'">
                        <label for="password_confirmation">Re-enter Password</label>
                    </div>
                    <p id="password-match-msg" class="text-md text-white"></p>

                    <div class="relative w-80 input-box">
                        <span class="icon"><ion-icon name="person"></ion-icon></span>
                        <input type="text" name="name" id="name" placeholder=" " style="font-family: 'Poppins'">
                        <label for="name">Full Name</label>
                        @error('name')
                            <p class="absolute text-xs text-white -bottom-5 left-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="relative w-80 input-box">
                        <span class="icon"><ion-icon name="call"></ion-icon></span>
                        <input type="tel" pattern="[0-9]*" inputmode="numeric" name="tel" id="tel" placeholder=" " style="font-family: 'Poppins'">
                        <label for="tel">Phone/Tel Number</label>
                    </div>

                    <button type="submit" class="w-80 h-12 bg-white hover:bg-red-600 hover:text-white transition-colors duration-300 text-black text-xl rounded-full" style="font-family: 'Poppins'">
                        Register
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <script>
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('password_confirmation');
        const matchMsg = document.getElementById('password-match-msg');

        function validatePasswords() {
            if (password.value && confirmPassword.value) {
                if (password.value === confirmPassword.value) {
                    matchMsg.textContent = "Passwords match!";
                    matchMsg.style.color = "lightgreen";
                } else {
                    matchMsg.textContent = "Passwords do not match.";
                    matchMsg.style.color = "red";
                }
            } else {
                matchMsg.textContent = "";
            }
        }

        password.addEventListener('input', validatePasswords);
        confirmPassword.addEventListener('input', validatePasswords);
    </script>
</x-layout>
