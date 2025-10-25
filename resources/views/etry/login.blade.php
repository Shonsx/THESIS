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

    <div class=" h-screen overflow-hidden flex flex-col">
        <div class="flex-1 flex items-center justify-center bg-cover bg-center" style="background-image: url({{asset('images/BG.png')}})">
            <div class="container max-w-lg h-1/2 bg-[#8c8c8c]/20 backdrop-blur-md border-1 rounded-3xl flex flex-col">
                <div class="container w-full h-20 flex justify-center items-center text-center mt-5">
                    <h2 class="text-5xl text-black font-bold" style="font-family: 'Poppins'">Login</h2>
                </div>
                <div class="container w-full h-64 border-b-2 ">
                    <form action="{{ route('login') }}" method="POST" class="flex flex-col items-center text-center">
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
                        @if(session('error'))
                            <div class="text-center text-white bg-red-500 px-4 py-2 rounded-md mb-4">
                                {{ session('error') }}
                            </div>
                        @endif
                        <div class="remember-forgot">
                            <label><input type="checkbox" name="remember" value="1">Remember me</label>
                            <a href="{{ route('password.request') }}">Forgot Password?</a>
                        </div>
                        <button type="submit" class="w-80 h-12 bg-white hover:bg-[#FAC000] hover:text-black transition-colors duration-300 text-black text-xl rounded-full my-4 cursor-pointer" style="font-family: 'Poppins'">Login</button>
                        <button type="button" onclick="showAdminLogin()" class="w-80 h-12 bg-red-700 hover:bg-red-300 transition-colors duration-300 text-white text-xl rounded-full cursor-pointer" style="font-family: 'Poppins'">Admin Login</button>
                        <a href="/signup" class="text-white hover:text-red-500 mt-5" style="font-family: 'Poppins'">Don't have an account? Sign Up</a>
                    </form>
                </div>
                
            </div>
        </div>
    </div>

    <!-- Admin Login Modal -->
    <div id="adminModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-8 max-w-md w-full mx-4">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-2xl font-bold text-black" style="font-family: 'Poppins'">Admin Login</h3>
                <button onclick="closeAdminModal()" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <form action="{{ route('admin.login') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="admin_username" class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                    <input type="text" name="username" id="admin_username" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
                           style="font-family: 'Poppins'">
                </div>
                
                <div class="mb-6">
                    <label for="admin_password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                    <input type="password" name="password" id="admin_password" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
                           style="font-family: 'Poppins'">
                </div>
                
                <div class="flex gap-4">
                    <button type="submit" class="flex-1 bg-red-600 hover:bg-red-700 text-white py-2 px-4 rounded-md transition-colors duration-300" style="font-family: 'Poppins'">
                        Login as Admin
                    </button>
                    <button type="button" onclick="closeAdminModal()" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 py-2 px-4 rounded-md transition-colors duration-300" style="font-family: 'Poppins'">
                        Cancel
                    </button>
                </div>
                <div class="mt-4 text-center">
                    <a href="{{ route('admin.password.request') }}" class="text-red-600 hover:text-red-700" style="font-family: 'Poppins'">Forgot Admin Password?</a>
                </div>
            </form>
        </div>
    </div>

    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    
    <script>
        function showAdminLogin() {
            document.getElementById('adminModal').classList.remove('hidden');
        }
        
        function closeAdminModal() {
            document.getElementById('adminModal').classList.add('hidden');
            // Clear form fields
            document.getElementById('admin_username').value = '';
            document.getElementById('admin_password').value = '';
        }
        
        // Close modal when clicking outside
        document.getElementById('adminModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeAdminModal();
            }
        });
    </script>
</x-layout>