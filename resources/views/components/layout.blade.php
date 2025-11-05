<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>C-Spot ETRY | {{$title ?? ''}}</title>
    
    @vite('resources/css/app.css')

</head>
<body>
    <header>
        <nav class="bg-[#000000] py-2 px-4 relative z-40">
            <div class="flex justify-between items-center">
                @php
                    $role = auth()->check() ? auth()->user()->role : null;
                    $logoClickable = !in_array($role, ['admin','manager']);
                @endphp
                <div class="flex items-center space-x-2 {{ $logoClickable ? 'cursor-pointer' : '' }}" @if($logoClickable) onclick="window.location.href='/'" @endif>
                    <img src="{{asset('images/E-LOGO-removebg-preview.png')}}" alt="E-Try" class="w-auto h-15 scale-125">
                </div>
        
                <!-- Menu Items -->
                <ul id="menu" class="hidden md:flex md:items-center space-x-4">
                    @if(auth()->check())
                        @if(auth()->user()->role == 'admin')
                            <li>
                                <a href="{{ route('addProduct') }}" class="w-30 px-4 py-2 border bg-[#8c8c8c] text-center text-black rounded-xl transition duration-500 hover:bg-[#00c7c7] hover:text-white">Add Product</a>
                            </li>
                            <li>
                                <a href="{{ route('gcash.index') }}" class="w-30 px-4 py-2 border bg-[#007DFE] text-center text-white rounded-xl transition duration-500 hover:bg-[#00c7c7] hover:text-white">GCASH</a>
                            </li>
                        @elseif(auth()->user()->role == 'manager')
                            <li>
                                <a href="{{ route('cashier.main') }}" class="w-30 px-4 py-2 border bg-[#8c8c8c] text-center text-white rounded-xl transition duration-500 hover:bg-[#00c7c7] hover:text-white">Pending Orders</a>
                            </li>
                        @endif
                    @endif
                    <li>
                        @if(auth()->check() && auth()->user()->role == 'admin')
                            <a href="{{ route('admin.index') }}" 
                            class="text-white text-lg" 
                            style="font-family:'Poppins'">Dashboard</a>
                        @elseif(auth()->check() && auth()->user()->role == 'manager')
                            <a href="{{ route('manager.index') }}" 
                            class="text-white text-lg" 
                            style="font-family:'Poppins'">Dashboard</a>
                        @else
                            <a href="/product" 
                            class="text-white text-lg" 
                            style="font-family:'Poppins'">Products</a>
                        @endif
                    </li>

                    {{-- Search Bar --}}
                    <form action="{{ route('products.index') }}" method="GET" class="relative">
                        <input 
                            type="text" 
                            name="search"  
                            value="{{ request('search') }}" 
                            placeholder="Search" 
                            class="w-60 min-w-min px-4 py-2 pr-10 pl-3 border border-black rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
                            onkeydown="if(event.key === 'Enter') { this.form.submit(); }" /> <!-- Submit form on Enter -->
                        <div class="absolute inset-y-0 right-2 flex items-center">
                            <img src="{{ asset('icons/icons8-search.svg') }}" class="w-5 h-5">
                        </div>
                    </form>                    
                    @auth
                        <div class="relative">
                            <button class="focus:outline-none" onclick="toggleNotifications()" aria-expanded="false">
                                <img src="{{ asset('icons/bell-notification.svg') }}" alt="Notification Bell" class="w-7 h-7 cursor-pointer">
                            </button>
                            <!-- Notification Count Badge (real-time) -->
                            @php $initialUnread = auth()->check() ? optional(auth()->user())->unreadNotifications->count() : 0; @endphp
                            <span id="notificationBadge" class="absolute top-0 right-0 rounded-full bg-red-500 text-white text-xs w-5 h-5 flex items-center justify-center {{ ($initialUnread ?? 0) > 0 ? '' : 'hidden' }}">!</span>
                        
                            <!-- Notification Dropdown -->
                            <div id="notificationDropdown" class="absolute right-0 mt-2 w-72 bg-white border border-gray-300 rounded-lg shadow-lg hidden z-50" aria-hidden="true">
                                @if(auth()->check())
                                    <div class="flex justify-between items-center px-4 py-2 border-b bg-gray-100">
                                        <span class="text-sm font-semibold">Notifications</span>
                                        @if(auth()->user()->unreadNotifications->count())
                                            <form action="{{ route('notifications.readAll') }}" method="POST">
                                                @csrf
                                                <button type="submit" class="text-xs text-blue-600 hover:underline">Mark all as read</button>
                                            </form>
                                        @endif
                                    </div>
                                    <ul class="max-h-60 overflow-y-auto">
                                        @php
                                            // Show unread first, then read as history
                                            $unread = auth()->user()->unreadNotifications;
                                            $read = auth()->user()->readNotifications;
                                        @endphp
                                        @forelse($unread as $notification)
                                            @php $redirect = $notification->data['redirect'] ?? null; @endphp
                                            <li class="px-4 py-2 border-b" style="opacity: 1;">
                                                @if ($redirect)
                                                    <a href="{{ route('notifications.read', $notification->id) }}?redirect={{ urlencode($redirect) }}" class="block text-sm text-blue-700 hover:underline notification-link">
                                                        {{ $notification->data['message'] ?? ($notification->message ?? 'New notification') }}
                                                    </a>
                                                @else
                                                    <a href="{{ route('notifications.read', $notification->id) }}" class="block text-sm text-blue-700 hover:underline notification-link">
                                                        {{ $notification->data['message'] ?? ($notification->message ?? 'New notification') }}
                                                    </a>
                                                @endif
                                                <a href="{{ route('notifications.read', $notification->id) }}" class="text-xs text-blue-600">Mark as read</a>
                                            </li>
                                        @empty
                                            <li class="px-4 py-2 text-sm text-gray-500">No new notifications</li>
                                        @endforelse
                                        @foreach($read as $notification)
                                            @php $redirect = $notification->data['redirect'] ?? null; @endphp
                                            <li class="px-4 py-2 border-b" style="opacity: 0.7;">
                                                @if ($redirect)
                                                    <a href="{{ $redirect }}" class="block text-sm text-blue-700 hover:underline notification-link">
                                                        {{ $notification->data['message'] ?? ($notification->message ?? 'Notification') }}
                                                    </a>
                                                @else
                                                    <p class="text-sm">{{ $notification->data['message'] ?? ($notification->message ?? 'Notification') }}</p>
                                                @endif
                                                <span class="text-[10px] text-gray-500">{{ $notification->created_at->diffForHumans() }}</span>
                                            </li>
                                        @endforeach
                                    </ul>                                    
                                @else
                                    <p class="px-4 py-2 text-sm text-gray-500">You need to be logged in to see notifications.</p>
                                @endif
                            </div>                            
                        </div>

                        @if(auth()->user()->role === 'customer')
                        <!-- Shopping Cart Icon (directs to cart page) -->
                        <div class="relative">
                            <a href="{{ route('cart.show') }}" class="focus:outline-none" aria-label="Go to Cart">
                                <img src="{{ asset('icons/Shopping-bag.svg') }}" alt="Shopping Cart" class="w-9 h-9 cursor-pointer">
                            </a>
                            <!-- Cart Count Badge (real-time) -->
                            @php
                                $cartCount = \App\Models\Cart::where('user_id', auth()->id())->count();
                            @endphp
                            <span id="cartBadge" class="absolute top-0 right-0 rounded-full bg-red-500 text-white text-xs w-5 h-5 flex items-center justify-center {{ $cartCount > 0 ? '' : 'hidden' }}">{{ $cartCount }}</span>
                        </div>
                        @endif

                        <h2 class="text-white">{{Auth::user()->name}}</h2>
                        <div class="relative">
                            <button id="dropdownBtnDesktop" class="focus:outline-none">
                                <img src="{{ asset('icons/arrow-drop-down-svgrepo-com(w).svg') }}" alt="Dropdown" class="w-10 h-10 cursor-pointer transition duration-500 hover:bg-[#00c7c7] rounded-full" />
                            </button>
                            <div id="dropdownMenuDesktop" class="absolute right-0 mt-2 w-40 bg-white border border-gray-300 rounded-lg shadow-lg hidden z-50">
                                <a href="{{ route('account') }}" class="block px-4 py-2 hover:bg-gray-100">Account</a>
                                @if(auth()->user()->role == 'admin')
                                    <a href="{{ route('settings.admin') }}" class="block px-4 py-2 hover:bg-gray-100">Settings</a>
                                @elseif(auth()->user()->role == 'customer')
                                    <a href="{{ route('settings.account') }}" class="block px-4 py-2 hover:bg-gray-100">Settings</a>
                                @endif
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full text-left px-4 py-2 hover:bg-gray-100">Logout</button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="/login" class="w-30 px-4 py-2 border bg-[#8c8c8c] text-center text-black rounded-xl transition duration-500 hover:bg-[#FAC000] hover:text-white">Login</a>
                        <a href="/signup" class="w-30 px-4 py-2 border bg-[#8c8c8c] text-center text-black rounded-xl transition duration-500 hover:bg-[#FAC000] hover:text-white">Register</a>
                    @endauth
                </ul>
                <!-- Mobile Menu Button (moved inside for right alignment on small screens) -->
                <button id="menu-btn" class="md:hidden text-white text-2xl focus:outline-none">
                    ☰
                </button>
            </div>

            <!-- Mobile Menu Button -->
            <!-- removed: moved inside header row to align on right for small screens -->
        
            <!-- Mobile Menu -->
        <ul id="mobile-menu" class="hidden flex-col items-center space-y-4 mt-4 bg-[#000000] p-4 rounded-lg w-full">
            @if(auth()->check() && auth()->user()->role == 'admin')
                <li>
                    <a href="{{ route('addProduct') }}" class="w-30 px-4 py-2 border bg-[#8c8c8c] text-center text-white rounded-xl transition duration-500 hover:bg-[#00c7c7] hover:text-black">
                        Add Product
                    </a>
                </li>
            @endif
            <li>
                @if(auth()->check() && auth()->user()->role == 'admin')
                    <a href="{{ route('admin.index') }}" class="text-white text-lg" style="font-family:'Poppins'">Dashboard</a>
                @elseif(auth()->check() && auth()->user()->role == 'manager')
                    <a href="{{ route('manager.index') }}" class="text-white text-lg" style="font-family:'Poppins'">Dashboard</a>
                @else
                    <a href="/product" class="text-white text-lg" style="font-family:'Poppins'">Products</a>
                @endif
            </li>

            <!-- Search Bar -->
            <form action="{{ route('products.index') }}" method="GET" class="relative w-full">
            <input type="text" name="search" placeholder="Search" 
                     class="w-full px-4 py-2 pr-10 border border-black rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                 <div class="absolute inset-y-0 right-3 flex items-center">
                     <img src="{{asset('icons/icons8-search.svg')}}" class="w-5 h-5">
                 </div>
             </form>

            @auth
                <!-- Mobile Notifications & Cart -->
                <div class="flex items-center justify-between w-full">
                    <div class="relative">
                        <button class="focus:outline-none" onclick="toggleNotifications()" aria-expanded="false">
                            <img src="{{ asset('icons/bell-notification.svg') }}" alt="Notifications" class="w-7 h-7 cursor-pointer">
                        </button>
                        @php $initialUnread = auth()->check() ? optional(auth()->user())->unreadNotifications->count() : 0; @endphp
                        <span id="notificationBadgeMobile" class="absolute top-0 right-0 rounded-full bg-red-500 text-white text-xs w-5 h-5 flex items-center justify-center {{ ($initialUnread ?? 0) > 0 ? '' : 'hidden' }}">!</span>
                        <!-- Mobile Notification Dropdown -->
                        <div id="notificationDropdownMobile" class="absolute left-0 mt-2 w-72 bg-white border border-gray-300 rounded-lg shadow-lg hidden z-50" aria-hidden="true">
                            <div class="flex justify-between items-center px-4 py-2 border-b bg-gray-100">
                                <span class="text-sm font-semibold">Notifications</span>
                                @if(auth()->user()->unreadNotifications->count())
                                    <form action="{{ route('notifications.readAll') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="text-xs text-blue-600 hover:underline">Mark all as read</button>
                                    </form>
                                @endif
                            </div>
                            <ul class="max-h-60 overflow-y-auto">
                                @php $unread = auth()->user()->unreadNotifications; $read = auth()->user()->readNotifications; @endphp
                                @forelse($unread as $notification)
                                    @php $redirect = $notification->data['redirect'] ?? null; @endphp
                                    <li class="px-4 py-2 border-b" style="opacity: 1;">
                                        @if ($redirect)
                                            <a href="{{ route('notifications.read', $notification->id) }}?redirect={{ urlencode($redirect) }}" class="block text-sm text-blue-700 hover:underline notification-link">
                                                {{ $notification->data['message'] ?? ($notification->message ?? 'New notification') }}
                                            </a>
                                        @else
                                            <a href="{{ route('notifications.read', $notification->id) }}" class="block text-sm text-blue-700 hover:underline notification-link">
                                                {{ $notification->data['message'] ?? ($notification->message ?? 'New notification') }}
                                            </a>
                                        @endif
                                        <a href="{{ route('notifications.read', $notification->id) }}" class="text-xs text-blue-600">Mark as read</a>
                                    </li>
                                @empty
                                    <li class="px-4 py-2 text-sm text-gray-500">No new notifications</li>
                                @endforelse
                                @foreach($read as $notification)
                                    @php $redirect = $notification->data['redirect'] ?? null; @endphp
                                    <li class="px-4 py-2 border-b" style="opacity: 0.7;">
                                        @if ($redirect)
                                            <a href="{{ $redirect }}" class="block text-sm text-blue-700 hover:underline notification-link">
                                                {{ $notification->data['message'] ?? ($notification->message ?? 'Notification') }}
                                            </a>
                                        @else
                                            <p class="text-sm">{{ $notification->data['message'] ?? ($notification->message ?? 'Notification') }}</p>
                                        @endif
                                        <span class="text-[10px] text-gray-500">{{ $notification->created_at->diffForHumans() }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

                    @if(auth()->user()->role === 'manager')
                    <!-- Manager: Pending Orders quick button (mobile) -->
                    <div class="flex-shrink-0">
                        <a href="{{ route('cashier.main') }}" class="px-3 py-2 border bg-[#8c8c8c] text-white rounded-xl transition duration-300 hover:bg-[#00c7c7] hover:text-white text-sm">
                            Pending Orders
                        </a>
                    </div>
                    @endif

                    @if(auth()->user()->role === 'customer')
                    <div class="relative">
                        <a href="{{ route('cart.show') }}" class="focus:outline-none" aria-label="Go to Cart">
                            <img src="{{ asset('icons/Shopping-bag.svg') }}" alt="Shopping Cart" class="w-9 h-9 cursor-pointer">
                        </a>
                        @php $cartCount = \App\Models\Cart::where('user_id', auth()->id())->count(); @endphp
                        <span id="cartBadgeMobile" class="absolute top-0 right-0 rounded-full bg-red-500 text-white text-xs w-5 h-5 flex items-center justify-center {{ $cartCount > 0 ? '' : 'hidden' }}">{{ $cartCount }}</span>
                    </div>
                    @endif
                </div>

                <h2 class="text-white w-full">{{ Auth::user()->name }}</h2>
                <div class="relative w-full">
                    <button id="dropdownBtnMobile" class="focus:outline-none w-full text-left">
                        <img src="{{ asset('icons/arrow-drop-down-svgrepo-com(w).svg') }}" 
                            alt="Dropdown" 
                            class="w-10 h-10 cursor-pointer hover:bg-[#00c7c7] rounded-full"/>
                    </button>
                    <div id="dropdownMenuMobile" class="hidden bg-white border border-gray-300 rounded-lg shadow-lg w-full">
                        <a href="{{ route('account') }}" class="block px-4 py-2 hover:bg-gray-100">Account</a>
                        @if(auth()->user()->role == 'admin')
                            <a href="{{ route('settings.admin') }}" class="block px-4 py-2 hover:bg-gray-100">Settings</a>
                        @elseif(auth()->user()->role == 'customer')
                            <a href="{{ route('settings.account') }}" class="block px-4 py-2 hover:bg-gray-100">Settings</a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="block w-full text-left px-4 py-2 hover:bg-gray-100">Logout</button>
                        </form>
                    </div>
                </div>
            @else
                <a href="/login" class="w-30 px-4 py-2 border bg-[#B22222] text-center text-white rounded-xl transition duration-500 hover:bg-[#FAC000] hover:text-black">Login</a>
                <a href="/signup" class="w-30 px-4 py-2 border bg-[#B22222] text-center text-white rounded-xl transition duration-500 hover:bg-[#FAC000] hover:text-black">Register</a>
            @endauth
        </ul>
        </nav>
    </header>


    <main class="box-border">
        {{ $slot }}
    </main>

    @vite('resources/js/app.js')
    <script>
        // Removed duplicate mobile menu & account dropdown setup here.
        // These interactions are now handled exclusively in resources/js/app.js via Vite to avoid double-binding.

        // --------DROPDOWN FOR NOTIFICATION BELL--------
        function toggleNotifications() {
            const dropdownDesktop = document.getElementById('notificationDropdown');
            const dropdownMobile = document.getElementById('notificationDropdownMobile');
            [dropdownDesktop, dropdownMobile].forEach(dd => {
                if (dd) {
                    dd.classList.toggle('hidden');
                    const isExpanded = dd.classList.contains('hidden');
                    dd.setAttribute('aria-hidden', isExpanded);
                }
            });
        }

        // --------DROPDOWN FOR SHOPPING CART--------
        function toggleCart() {
            const dropdown = document.getElementById('cartDropdown');
            const button = document.querySelector('button[onclick="toggleCart()"]');
            dropdown.classList.toggle('hidden');
            const isExpanded = dropdown.classList.contains('hidden');
            button.setAttribute('aria-expanded', !isExpanded);
            dropdown.setAttribute('aria-hidden', isExpanded);
        }

        // Close notification and cart dropdowns when clicking outside
        document.addEventListener('click', function(event) {
            const notificationDropdowns = [document.getElementById('notificationDropdown'), document.getElementById('notificationDropdownMobile')].filter(Boolean);
            const notificationBellButtons = Array.from(document.querySelectorAll('button[onclick="toggleNotifications()"]'));
            const cartDropdown = document.getElementById('cartDropdown');
            const cartButton = document.querySelector('button[onclick="toggleCart()"]');

            // Determine if click was inside any notification dropdown or on any bell button
            const clickedInsideNotification = notificationDropdowns.some(dd => dd && dd.contains(event.target));
            const clickedOnBell = notificationBellButtons.some(btn => btn && btn.contains(event.target));

            // Close all notification dropdowns only when clicking outside both dropdowns and bells
            if (!clickedInsideNotification && !clickedOnBell) {
                notificationDropdowns.forEach(dd => dd.classList.add('hidden'));
            }

            // Close cart dropdown (if present) when clicking outside
            if (cartDropdown && cartButton) {
                const clickedInsideCart = cartDropdown.contains(event.target);
                const clickedOnCartButton = cartButton.contains(event.target);
                if (!clickedInsideCart && !clickedOnCartButton) {
                    cartDropdown.classList.add('hidden');
                }
            }
        });

        // Close dropdowns when clicking on links
        document.querySelectorAll('.notification-link').forEach(link => {
            link.addEventListener('click', function() {
                const dropdown = document.getElementById('notificationDropdown');
                if (dropdown) dropdown.classList.add('hidden');
            });
        });

        // --- Real-time badge updates for notifications and cart ---
        async function fetchJSON(url) {
            try {
                const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                if (!res.ok) return null;
                return await res.json();
            } catch (e) {
                return null;
            }
        }

        async function updateRealtimeBadges() {
            const notificationBadges = [document.getElementById('notificationBadge'), document.getElementById('notificationBadgeMobile')].filter(Boolean);
            const cartBadges = [document.getElementById('cartBadge'), document.getElementById('cartBadgeMobile')].filter(Boolean);

            const notifUrl = '{{ auth()->check() ? route('notifications.count') : '' }}';
            const cartUrl = '{{ auth()->check() ? route('cart.count') : '' }}';

            const [notifData, cartData] = await Promise.all([
                notifUrl ? fetchJSON(notifUrl) : Promise.resolve(null),
                cartUrl ? fetchJSON(cartUrl) : Promise.resolve(null)
            ]);

            if (notifData) {
                const unread = Number(notifData.unread || 0);
                notificationBadges.forEach(badge => {
                    if (unread > 0) {
                        badge.textContent = '!';
                        badge.classList.remove('hidden');
                    } else {
                        badge.classList.add('hidden');
                    }
                });
            }

            if (cartData) {
                const count = Number(cartData.count || 0);
                cartBadges.forEach(badge => {
                    if (count > 0) {
                        badge.textContent = String(count);
                        badge.classList.remove('hidden');
                    } else {
                        badge.classList.add('hidden');
                    }
                });
            }
        }

        // Refresh every 5 seconds and on tab focus
        updateRealtimeBadges();
        setInterval(updateRealtimeBadges, 5000);
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) updateRealtimeBadges();
        });
    </script>
</body>
</html>

