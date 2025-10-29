<style>
@keyframes scroll-horizontal {
    0% {
        transform: translateX(0%);
    }
    100% {
        transform: translateX(-50%);
    }
}

.animate-scroll-carousel {
    animation: scroll-horizontal 10s linear infinite;
    display: flex;
    white-space: nowrap;
}

.group-hover\:paused:hover {
    animation-play-state: paused;
}
</style>



<x-layout title="Home">
    <div class="w-full h-130 mt-10 border-b border-gray-400 flex justify-between items-center">
        <div class="w-1/2 h-full flex justify-center items-center">
            <div class="w-1/2 h-1/2 flex flex-col justify-center items-start">
                <h1 class="text-4xl font-semibold" style="font-family:'Poppins'">WELCOME TO OUR STORE</h1>
                <p class="text-m mt-2" style="font-family:'Poppins'">Try On Clothes Virtually! Experience our augmented reality feature for a new way of shopping.</p>
                <div class="flex gap-4 mt-4 w-full">
                    <a href="/signup" class="w-55 h-12 flex items-center justify-center font-semibold border border-black rounded-lg px-6 transition duration-300 bg-white text-black hover:bg-black hover:text-white">
                        Learn More
                    </a>
                    <a href="{{ route('products.index') }}" class="w-55 h-12 flex items-center justify-center font-semibold bg-[#FAC000] text-black rounded-lg px-6 transition duration-300 hover:bg-black hover:text-white">
                        Explore
                    </a>
                </div>
                
                
            </div>            
        </div>
        <div class="w-1/2 h-full flex justify-center items-center">
            <img src="{{ asset('images/backgroundLogo.jpg') }}" class="max-w-[90%] max-h-[90%] object-contain">
        </div>
    </div>

    <div class="w-full h-auto mt-10 flex flex-col items-center">
        <div class="max-w-[1200px] w-full h-auto flex flex-col md:flex-row items-center justify-between px-5">
            <div class="w-full md:w-1/2 h-full flex flex-col justify-center text-center md:text-left">
                <h1 class="text-lg md:text-4xl pb-3 md:pb-5 font-bold" style="font-family: 'Poppins'">Featured Products</h1>
                <p class="text-sm md:text-lg pb-3 md:pb-5 font-bold" style="font-family: 'Poppins'">Browse our latest collection</p>
                <a href="{{ route('products.index') }}" class="!w-[250px] md:w-[200px] h-10 md:h-12 flex items-center justify-center font-semibold bg-[#FAC000] text-white rounded-lg px-4 md:px-6 transition duration-300 hover:bg-black hover:text-white mx-auto md:mx-0">
                    Shop Now
                </a>
                
            </div>
            <div class="w-full md:w-1/2 h-auto flex justify-center md:justify-end">
                <img src="{{ asset('images/featuredProducts.jpg') }}" class="w-full md:w-auto md:h-[250px] object-contain">
            </div>
        </div>
    
        <!-- BELOW the "Featured Products" -->
        <div class="w-full bg-white py-12 flex justify-center">
            <div class="relative w-full max-w-[1200px] overflow-hidden group">
                <div class="flex gap-6 animate-scroll-carousel group-hover:paused">
                    @foreach($products as $product)
                        <div class="min-w-[250px] max-w-[250px] h-[250px] bg-white border rounded-lg shadow-lg overflow-hidden relative group/item hover:scale-105 transition-transform duration-300">
                            <!-- Product Image -->
                            <img src="{{ route('files.public', ['path' => $product->image]) }}" alt="{{ $product->name }}" class="w-full h-48 object-contain">

                            <!-- Blur Overlay on Hover -->
                            <div class="absolute inset-0 backdrop-blur-sm bg-white/30 opacity-0 group-hover/item:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                                <h3 class="text-black text-lg font-extrabold text-center px-2">{{ $product->name }}</h3>
                            </div>
                        </div>
                    @endforeach

                    <!-- Duplicate for seamless looping -->
                    @foreach($products as $product)
                        <div class="min-w-[250px] max-w-[250px] h-[250px] bg-white border rounded-lg shadow-lg overflow-hidden relative group/item hover:scale-105 transition-transform duration-300">
                            <img src="{{ route('files.public', ['path' => $product->image]) }}" alt="{{ $product->name }}" class="w-full h-48 object-contain">

                            <div class="absolute inset-0 backdrop-blur-sm bg-white/30 opacity-0 group-hover/item:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                                <h3 class="text-black text-lg font-extrabold text-center px-2">{{ $product->name }}</h3>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Our Store Grid Section -->
        <section class="w-full bg-white py-12">
            <div class="max-w-[1200px] mx-auto px-6">
                <h2 class="text-2xl md:text-4xl font-bold text-center mb-6" style="font-family:'Poppins'">Our store</h2>
                <p class="text-center text-sm md:text-base mb-10 opacity-80" style="font-family:'Poppins'">Step inside the Halang Branch — where style meets comfort.</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <img src="{{ asset('images/Store1_BG.jpg') }}" alt="Store photo 1" class="w-full h-[240px] md:h-[300px] object-cover rounded-lg shadow hover:scale-[1.02] transition-transform duration-300" />
                    <img src="{{ asset('images/Store2_BG.jpg') }}" alt="Store photo 2" class="w-full h-[240px] md:h-[300px] object-cover rounded-lg shadow hover:scale-[1.02] transition-transform duration-300" />
                    <img src="{{ asset('images/Store3_BG.jpg') }}" alt="Store photo 3" class="w-full h-[240px] md:h-[300px] object-cover rounded-lg shadow hover:scale-[1.02] transition-transform duration-300" />
                    <img src="{{ asset('images/Store4_BG.jpg') }}" alt="Store photo 4" class="w-full h-[240px] md:h-[300px] object-cover rounded-lg shadow hover:scale-[1.02] transition-transform duration-300" />
                </div>
                
                <!-- Social Media Links -->
                <div class="flex justify-center items-center gap-6 mt-8">
                    <a href="https://www.facebook.com/cspotblvd" target="_blank" rel="noopener noreferrer" class="flex items-center gap-3 px-6 py-3 bg-[#1877F2] text-white rounded-lg hover:bg-[#166FE5] transition-colors duration-300 shadow-md hover:shadow-lg">
                        <img src="{{ asset('icons/facebook.svg') }}" alt="Facebook" class="w-6 h-6 text-white">
                        <span class="font-semibold" style="font-family:'Poppins'">Follow us on Facebook</span>
                    </a>
                    <a href="https://www.instagram.com/cspotblvd/" target="_blank" rel="noopener noreferrer" class="flex items-center gap-3 px-6 py-3 bg-gradient-to-r from-[#E4405F] via-[#F56040] to-[#FFDC80] text-white rounded-lg hover:from-[#D73653] hover:via-[#E55A3C] hover:to-[#F0D078] transition-all duration-300 shadow-md hover:shadow-lg">
                        <img src="{{ asset('icons/instagram.svg') }}" alt="Instagram" class="w-6 h-6 text-white">
                        <span class="font-semibold" style="font-family:'Poppins'">Follow us on Instagram</span>
                    </a>
                </div>
            </div>
        </section>

        <!-- Engaging content below the store photos -->
        <section class="w-full bg-white pb-14">
            <div class="max-w-[1200px] mx-auto px-6">
                <!-- Intro -->
                <div class="text-center mb-8">
                    <h3 class="text-xl md:text-3xl font-bold" style="font-family:'Poppins'">Discover more in-store</h3>
                    <p class="text-sm md:text-base mt-2 opacity-80" style="font-family:'Poppins'">Hands-on fits, friendly stylists, and fresh drops — every visit feels special.</p>
                </div>

                <!-- Feature cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="border rounded-xl p-6 shadow-sm hover:shadow-md transition">
                        <h4 class="text-lg font-semibold mb-2" style="font-family:'Poppins'">AR Try-On & Real Fitting</h4>
                        <p class="text-sm opacity-80" style="font-family:'Poppins'">Preview styles with augmented reality, then feel the perfect fit in person.</p>
                    </div>
                    <div class="border rounded-xl p-6 shadow-sm hover:shadow-md transition">
                        <h4 class="text-lg font-semibold mb-2" style="font-family:'Poppins'">New Drops Weekly</h4>
                        <p class="text-sm opacity-80" style="font-family:'Poppins'">Stay ahead of trends with fresh arrivals and curated local favorites.</p>
                    </div>
                    <div class="border rounded-xl p-6 shadow-sm hover:shadow-md transition">
                        <h4 class="text-lg font-semibold mb-2" style="font-family:'Poppins'">Friendly Style Guidance</h4>
                        <p class="text-sm opacity-80" style="font-family:'Poppins'">Our team helps you build looks that match your vibe and budget.</p>
                    </div>
                </div>

                <!-- Testimonial -->
                <div class="mt-10 bg-[#F8F8F8] rounded-xl p-6 md:p-8">
                    <p class="text-base md:text-lg italic" style="font-family:'Poppins'">“Love the vibe at Halang Branch — tried on outfits with AR, then found my perfect fit with the help of their stylists. Definitely coming back!”</p>
                    <p class="mt-2 text-sm opacity-70" style="font-family:'Poppins'">— Happy customer</p>
                </div>

                <!-- CTA Row -->
                <div class="mt-8 flex flex-col md:flex-row items-center justify-between gap-4">
                    <div class="text-center md:text-left">
                        <h5 class="text-base md:text-lg font-semibold" style="font-family:'Poppins'">Planning a visit?</h5>
                        <p class="text-sm opacity-80" style="font-family:'Poppins'">Tap below for directions to our Halang Branch.</p>
                    </div>
                    <a href="https://www.google.com/maps?q=14.1971982,121.1621428" target="_blank" rel="noopener" class="inline-flex items-center justify-center font-semibold bg-[#FAC000] text-black rounded-lg px-6 h-12 transition duration-300 hover:bg-black hover:text-white">
                        Get Directions
                    </a>
                </div>
            </div>
        </section>

        <footer class="w-full bg-[#000000] text-white mt-6">
            <div class="w-full px-6 md:px-10 py-10 text-center">
                <h2 class="text-3xl md:text-4xl font-bold mb-2" style="font-family:'Poppins'">Come visit us</h2>
                <p class="text-sm md:text-base opacity-80" style="font-family:'Poppins'">Find us on Google Maps</p>
            </div>
            <div class="w-full h-[420px] md:h-[500px]">
                <iframe
                    title="Store Location Map"
                    src="https://www.google.com/maps?q=14.1971982,121.1621428&z=17&output=embed"
                    width="100%"
                    height="100%"
                    style="border:0; display:block;"
                    allowfullscreen
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
        </footer>

</x-layout>