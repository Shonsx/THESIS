<x-layout title="GCash">
    <div class="container mx-auto flex flex-col items-center mt-20 px-4">
        <h1 class="text-4xl font-bold mb-8 text-center">GCash Account</h1>

        @if(session('success'))
            <div class="bg-green-200 text-green-800 px-4 py-2 rounded mb-6 w-full max-w-md text-center">
                {{ session('success') }}
            </div>
        @endif

        @if($gcash?->image_path)
            <div class="mb-6 text-center">
                <h5 class="font-semibold mb-3">Current GCash Image:</h5>
                <img src="{{ asset('storage/' . $gcash->image_path) }}" alt="GCash QR"
                     class="mx-auto rounded shadow max-w-xs">
            </div>
        @else
            <p class="text-gray-500 mb-6">No GCash image uploaded yet.</p>
        @endif

        <form action="{{ route('gcash.store') }}" method="POST" enctype="multipart/form-data"
              class="w-full max-w-md flex flex-col items-center">
            @csrf
            <input
                type="file"
                name="gcash_image"
                class="mb-4 block w-full rounded border border-gray-300 p-2"
                required
            />
            <button
                type="submit"
                class="w-full bg-gray-800 text-white py-2 rounded hover:bg-cyan-500 transition duration-300"
            >
                Upload GCash Image
            </button>
        </form>
    </div>
</x-layout>
