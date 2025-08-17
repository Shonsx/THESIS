<x-layout title="Order History">
    <style>
        body {
            position: relative;
            min-height: 100vh;
            margin: 0;
        }

        body::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url('{{ asset('images/BG-2.jpg') }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            opacity: 0.3; 
            z-index: -1;
        }
    </style>
    <div class="container mx-auto px-4 py-6">
        <h2 class="text-xl font-bold mb-4">Order History</h2>

        @if(session('success'))
            <p class="text-green-600 mb-4">{{ session('success') }}</p>
        @endif

        <div class="bg-white p-4 rounded-lg shadow-md mx-auto w-full">
            <div class="flex justify-end items-center gap-2 mb-4">
                <!-- Customer Name Search -->
                <input 
                    type="text" 
                    id="searchInput" 
                    placeholder="Search Customer Name" 
                    class="border border-gray-300 rounded px-3 py-1 focus:outline-none focus:ring focus:border-blue-300"
                >

                <!-- Date Filter -->
                <select 
                    id="dateFilter" 
                    class="border border-gray-300 rounded px-3 py-1 focus:outline-none focus:ring focus:border-blue-300"
                >
                    <option value="all">All Dates</option>
                    <option value="today">Today</option>
                    <option value="thisWeek">This Week</option>
                    <option value="custom">Custom Date</option>
                </select>

                <!-- Custom Date Picker -->
                <input 
                    type="date" 
                    id="customDate" 
                    class="border border-gray-300 rounded px-3 py-1 hidden"
                >
            </div>

            <table class="table-auto w-full border-collapse border border-gray-300">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="border px-4 py-2 text-center">Customer Name</th>
                        <th class="border px-4 py-2 text-center">Address</th>
                        <th class="border px-4 py-2 text-center">Product Name</th>
                        <th class="border px-4 py-2 text-center">Size</th>
                        <th class="border px-4 py-2 text-center">Quantity</th>
                        <th class="border px-4 py-2 text-center">Total Price</th>
                        <th class="border px-4 py-2 text-center">Completed At</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($processedOrders as $order)
                        <tr>
                            <td class="border px-4 py-2 text-center">{{ $order->user->name ?? 'No user' }}</td>
                            <td class="border px-4 py-2 text-center">
                                @if ($order->user && $order->user->address)
                                    {{ $order->user->address->address }},
                                    {{ $order->user->address->city }},
                                    {{ $order->user->address->state }},
                                    {{ $order->user->address->zip }}
                                @else
                                    No address
                                @endif
                            </td>
                            <td class="border px-4 py-2 text-center">{{ $order->product->name ?? 'No product' }}</td>
                            <td class="border px-4 py-2 text-center">{{ strtoupper($order->size) }}</td>
                            <td class="border px-4 py-2 text-center">{{ $order->quantity }}</td>
                            <td class="border px-4 py-2 text-center">₱{{ number_format($order->total_price, 2) }}</td>
                            <td class="border px-4 py-2 text-center">
                                <span class="completed-date" data-datetime="{{ $order->updated_at->setTimezone('Asia/Manila')->toISOString() }}">
                                    {{ $order->updated_at->setTimezone('Asia/Manila')->format('F d, Y h:i A') }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($processedOrders->isEmpty())
            <p class="text-gray-500 text-center mt-4">No completed orders.</p>
        @endif
    </div>

    <script>
        const searchInput = document.getElementById('searchInput');
        const dateFilter = document.getElementById('dateFilter');
        const customDateInput = document.getElementById('customDate');

        dateFilter.addEventListener('change', function () {
            customDateInput.classList.toggle('hidden', this.value !== 'custom');
            filterTable();
        });

        [searchInput, dateFilter, customDateInput].forEach(input => {
            input.addEventListener('input', filterTable);
        });

        function filterTable() {
            const nameFilter = searchInput.value.toLowerCase();
            const selectedDate = dateFilter.value;
            const customDate = customDateInput.value;
            const rows = document.querySelectorAll("table tbody tr");

            const today = new Date();
            today.setHours(0, 0, 0, 0);

            const startOfWeek = new Date(today);
            startOfWeek.setDate(today.getDate() - today.getDay());
            const endOfWeek = new Date(startOfWeek);
            endOfWeek.setDate(startOfWeek.getDate() + 6);
            endOfWeek.setHours(23, 59, 59, 999);

            rows.forEach(row => {
                const customerName = row.children[0].textContent.toLowerCase();
                const dateAttr = row.querySelector(".completed-date").getAttribute("data-datetime");
                const rowDate = new Date(dateAttr);

                let matchesName = customerName.includes(nameFilter);
                let matchesDate = true;

                if (selectedDate === 'today') {
                    matchesDate = rowDate.toDateString() === today.toDateString();
                } else if (selectedDate === 'thisWeek') {
                    matchesDate = rowDate >= startOfWeek && rowDate <= endOfWeek;
                } else if (selectedDate === 'custom' && customDate) {
                    const custom = new Date(customDate);
                    matchesDate = rowDate.toDateString() === custom.toDateString();
                }

                row.style.display = (matchesName && matchesDate) ? '' : 'none';
            });
        }
    </script>
</x-layout>
