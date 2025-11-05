<x-layout title="Admin Settings">
    <style>
        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #000;
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #e5e5e5;
        }

        input[type="text"] {
            border: 1px solid #ccc;
            padding: 5px 10px;
            border-radius: 5px;
            width: 200px;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.9); }
            to { opacity: 1; transform: scale(1); }
        }

        .animate-fade-in {
            animation: fadeIn 0.3s ease-out;
        }

        /* Mobile responsiveness */
        @media (max-width: 640px) {
            .container {
                padding: 12px;
            }
            .header {
                flex-direction: column;
                align-items: stretch;
            }
            input[type="text"] {
                width: 100%;
            }
            th, td {
                font-size: 0.85rem;
                padding: 8px;
            }
        }
    </style>

    <div class="container">
        <div class="header py-5">
            <h2 class="text-xl font-bold">User List</h2>
            <input type="text" id="searchInput" placeholder="Search user..." onkeyup="filterUsers()">
        </div>

        @if(session('success'))
            <div id="successModal" class="fixed inset-0 flex items-center justify-center z-50">
                <div class="bg-green-500 text-white text-center px-6 py-4 rounded-lg shadow-lg animate-fade-in">
                    {{ session('success') }}
                </div>
            </div>
        @endif

        <div class="overflow-x-auto">
        <table class="min-w-[640px] w-full border">
            <thead>
                <tr class="bg-gray-200">
                    <th class="p-2">Name</th>
                    <th class="p-2">Phone</th>
                    <th class="p-2">Role</th>
                    <th class="p-2">Action</th>
                </tr>
            </thead>
            <tbody id="userTable">
                @foreach($users as $user)
                    @if($user->id !== 1) 
                    <tr class="border-t user-row">
                        <td class="p-2 user-name">{{ $user->name }}</td>
                        <td class="p-2">{{ $user->tel }}</td>
                        <td class="p-2">{{ $user->role }}</td>
                        <td class="p-2">
                            <form action="{{ route('update-role', $user->id) }}" method="POST">
                                @csrf
                                <select name="role" class="border rounded p-1 w-auto">
                                    <option value="manager" {{ $user->role == 'manager' ? 'selected' : '' }}>Manager</option>
                                    <option value="customer" {{ $user->role == 'customer' ? 'selected' : '' }}>Customer</option>
                                </select>
                                <button type="submit" class="mt-2 sm:mt-0 sm:ml-2 bg-blue-500 text-white px-2 py-1 rounded w-auto">Update</button>
                            </form>
                        </td>
                    </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
        </div>
    </div>

    <script>
        function filterUsers() {
            let input = document.getElementById("searchInput").value.toLowerCase();
            let rows = document.querySelectorAll(".user-row");

            rows.forEach(row => {
                let name = row.querySelector(".user-name").textContent.toLowerCase();
                row.style.display = name.includes(input) ? "" : "none";
            });
        }
        setTimeout(() => {
            const modal = document.getElementById('successModal');
            if (modal) {
                modal.style.display = 'none';
            }
        }, 3000);
    </script>
</x-layout>
