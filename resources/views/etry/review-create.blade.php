<x-layout title="Write a Review">
    <div class="min-h-screen bg-cover bg-center" style="background-image: url({{ asset('images/BG.png') }})">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 w-full py-10">
            <div class="bg-white rounded-lg shadow-2xl p-6 md:p-8">
                <div class="flex items-center space-x-4 mb-6">
                    <img src="{{ route('files.public', ['path' => $product->image]) }}" alt="{{ $product->name }}" class="w-20 h-20 object-contain rounded">
                    <div>
                        <h2 class="text-2xl font-bold">Review: {{ $product->name }}</h2>
                        <p class="text-gray-600">Share your thoughts and help others decide.</p>
                    </div>
                </div>

                <form action="{{ route('reviews.store', $product->id) }}" method="POST" class="space-y-6">
                    @csrf

                    <div>
                        <label class="block font-semibold mb-2">Your Rating</label>
                        <!-- Interactive Star Rating (supports 0.5 increments) -->
                        <input type="hidden" name="rating" id="rating" value="{{ old('rating', 5) }}" />
                        <div id="star-widget" class="flex items-center select-none" style="gap: 6px;">
                            <!-- 5 stars rendered; we will control fill via JS -->
                            @for($i=1; $i<=5; $i++)
                                <div class="star relative text-3xl cursor-pointer" data-index="{{ $i }}" style="width: 32px; height: 32px;">
                                    <span class="star-empty" style="color: #d1d5db; position: absolute; left: 0; top: 0;">★</span>
                                    <span class="star-fill" style="color: #f59e0b; position: absolute; left: 0; top: 0; width: 0; overflow: hidden;">★</span>
                                </div>
                            @endfor
                            <span id="rating-text" class="ml-2 text-sm text-gray-600">5.0</span>
                        </div>
                        @error('rating')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block font-semibold mb-2">Comment</label>
                        <textarea name="comment" rows="4" class="w-full border rounded px-3 py-2" placeholder="What did you like or not like?" maxlength="1000">{{ old('comment') }}</textarea>
                        @error('comment')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="bg-yellow-500 text-white px-6 py-2 rounded hover:bg-yellow-600">Submit Review</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layout>

<script>
    (function() {
        const widget = document.getElementById('star-widget');
        if (!widget) return;

        const hiddenInput = document.getElementById('rating');
        const ratingText = document.getElementById('rating-text');
        const stars = Array.from(widget.querySelectorAll('.star'));
        const STAR_SIZE = 32; // px

        // Initialize with existing value
        let selected = Math.max(0.5, Math.min(5, parseFloat(hiddenInput.value || '5')));
        render(selected);

        function render(value) {
            // Update UI: fill widths per star
            stars.forEach((star, idx) => {
                const starIndex = idx + 1;
                const fill = Math.max(0, Math.min(1, value - (starIndex - 1))); // 0..1 per star
                const fillEl = star.querySelector('.star-fill');
                fillEl.style.width = (fill * 100) + '%'; // 0, 50, 100
            });
            hiddenInput.value = value.toFixed(1);
            if (ratingText) ratingText.textContent = value.toFixed(1);
        }

        function computeValueFromPointer(evt) {
            const rect = widget.getBoundingClientRect();
            const x = evt.clientX - rect.left; // relative pointer
            // Clamp within total width (5 stars * STAR_SIZE + gaps). We'll use star offsets.
            // Find which star is under pointer
            let index = Math.floor(x / (STAR_SIZE + 6)) + 1; // 6px gap matches inline style
            index = Math.max(1, Math.min(5, index));
            const star = stars[index - 1];
            const sRect = star.getBoundingClientRect();
            const within = evt.clientX - sRect.left; // within the star
            const half = within < (STAR_SIZE / 2) ? 0.5 : 1.0;
            return index - 1 + half; // 0.5..5.0
        }

        // Hover effects
        stars.forEach(star => {
            star.addEventListener('mousemove', evt => {
                const val = computeValueFromPointer(evt);
                render(val);
            });
            star.addEventListener('mouseleave', () => {
                render(selected);
            });
            star.addEventListener('click', evt => {
                selected = computeValueFromPointer(evt);
                render(selected);
            });
        });
    })();
</script>