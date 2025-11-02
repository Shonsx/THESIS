<x-layout title="Log-In">
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
        .remember-forgot {
            color: black;
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
            color: black;
            text-decoration: none; 
        }
        .remember-forgot input {
            margin-right: 5px; 
        }
        .remember-forgot label {
            margin-left: 5px; 
        }
    </style>

    <div class="h-screen overflow-hidden flex flex-col">
        <div class="flex-1 flex items-center justify-center bg-cover bg-center" style="background-image: url({{asset('images/BG.png')}})">
            <div class="container max-w-lg w-full bg-[#8c8c8c]/20 backdrop-blur-md border border-white/20 rounded-3xl flex flex-col p-6 md:p-8 shadow-lg">
                <div class="w-full flex justify-center items-center text-center">
                    <h2 class="text-4xl md:text-5xl text-black font-bold" style="font-family: 'Poppins'">Login</h2>
                </div>
                
                <form action="{{ route('login') }}" method="POST" class="flex flex-col items-center text-center mt-4">
                        @csrf
                        <div class="relative my-2 w-80 input-box">
                            <span class="icon"><ion-icon name="mail"></ion-icon></span>
                            <input type="email" name="email" id="email" placeholder=" " style="font-family: 'Poppins'">
                            <label for="email">Email</label>
                        </div>
                        <div class="relative my-2 w-80 input-box">
                            <span class="icon"><ion-icon name="lock-closed"></ion-icon></span>
                            <input type="password" name="password" id="password" placeholder=" " style="font-family: 'Poppins'">
                            <label for="password">Password</label>
                        </div>
                        
                        <div class="remember-forgot">
                            <label><input type="checkbox" name="remember" value="1">Remember me</label>
                            <a href="{{ route('password.request') }}">Forgot Password?</a>
                        </div>
                        <button type="submit" class="w-80 h-12 bg-white hover:bg-[#FAC000] hover:text-black transition-colors duration-300 text-black text-xl rounded-full my-4 cursor-pointer" style="font-family: 'Poppins'">Login</button>
                        <button type="button" onclick="showAdminLogin()" class="w-80 h-12 bg-red-700 hover:bg-red-300 transition-colors duration-300 text-white text-xl rounded-full cursor-pointer" style="font-family: 'Poppins'">Admin Login</button>
                        <div class="w-full border-t-2 border-black my-4"></div>
                        <a href="/signup" class="text-black hover:text-red-500 mt-1" style="font-family: 'Poppins'">Don't have an account? Sign Up</a>
                    </form>
                </div>
                
            </div>
        </div>
    </div>

    <!-- Admin Login Modal (Redesigned) -->
    <div id="adminModal" class="fixed inset-0 hidden items-center justify-center z-50">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeAdminModal()"></div>
        <!-- Dialog -->
        <div class="relative bg-white rounded-2xl w-[92%] max-w-md shadow-2xl overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b">
                <div>
                    <h3 class="text-xl md:text-2xl font-bold text-black" style="font-family: 'Poppins'">Admin Login</h3>
                    <p class="text-sm text-gray-500" style="font-family: 'Poppins'">Enter your admin credentials</p>
                </div>
                <button onclick="closeAdminModal()" class="text-gray-500 hover:text-gray-700 p-2 rounded-full hover:bg-gray-100" aria-label="Close admin login">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form action="{{ route('admin.login') }}" method="POST" class="px-6 py-5">
                @csrf
                <div class="mb-4">
                    <label for="admin_username" class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                    <div class="relative">
                        <input type="text" name="username" id="admin_username" required 
                               class="w-full pl-3 pr-10 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
                               style="font-family: 'Poppins'">
                        <span class="absolute inset-y-0 right-3 flex items-center text-gray-400">
                            <ion-icon name="person-outline" class="text-gray-400 text-xl"></ion-icon>
                        </span>
                    </div>
                </div>
                
                <div class="mb-6">
                    <label for="admin_password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                    <div class="relative">
                        <input type="password" name="password" id="admin_password" required 
                               class="w-full pl-3 pr-10 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
                               style="font-family: 'Poppins'">
                        <span class="absolute inset-y-0 right-3 flex items-center text-gray-400">
                            <ion-icon name="lock-closed-outline" class="text-gray-400 text-xl"></ion-icon>
                        </span>
                    </div>
                </div>
                
                <div class="flex">
                    <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white py-2 px-4 rounded-lg transition-colors duration-300" style="font-family: 'Poppins'">
                        Login as Admin
                    </button>
                </div>
                <div class="mt-4 text-center">
                    <a href="{{ route('admin.password.request') }}" class="text-red-600 hover:text-red-700" style="font-family: 'Poppins'">Forgot Admin Password?</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Transient Error Modals -->
    @if(session('error'))
    <div id="loginErrorModal" class="fixed inset-0 hidden items-center justify-center z-[60]">
        <div class="absolute inset-0 bg-black/40" onclick="hideModal('loginErrorModal')"></div>
        <div class="relative bg-red-600 text-white rounded-xl shadow-lg px-5 py-4 text-center">
            <p class="text-white font-medium" style="font-family: 'Poppins'">{{ session('error') }}</p>
        </div>
    </div>
    @endif
    @if(session('admin_error'))
    <div id="adminErrorModal" class="fixed inset-0 hidden items-center justify-center z-[60]">
        <div class="absolute inset-0 bg-black/40" onclick="hideModal('adminErrorModal')"></div>
        <div class="relative bg-red-600 text-white rounded-xl shadow-lg px-5 py-4 text-center">
            <p class="text-white font-medium" style="font-family: 'Poppins'">{{ session('admin_error') }}</p>
        </div>
    </div>
    @endif

    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    
    <script>
        function showAdminLogin() {
            const modal = document.getElementById('adminModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
        
        function closeAdminModal() {
            const modal = document.getElementById('adminModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            // Clear form fields
            const u = document.getElementById('admin_username');
            const p = document.getElementById('admin_password');
            if (u) u.value = '';
            if (p) p.value = '';
        }

        // Auto-dismiss error modals after 3 seconds
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
            if (document.getElementById('loginErrorModal')) {
                showTransientModal('loginErrorModal');
            }
            if (document.getElementById('adminErrorModal')) {
                showTransientModal('adminErrorModal');
            }
        });

        // ESC to close admin modal
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeAdminModal();
        });
    </script>
</x-layout>