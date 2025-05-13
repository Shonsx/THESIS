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
                    <a href="" class="w-55 h-12 flex items-center justify-center font-semibold border border-black rounded-lg px-6 transition duration-300 bg-white text-black hover:bg-black hover:text-white">
                        Learn More
                    </a>
                    <a href="" class="w-55 h-12 flex items-center justify-center font-semibold bg-[#FAC000] text-black rounded-lg px-6 transition duration-300 hover:bg-black hover:text-white">
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
                <a href="" class="!w-[250px] md:w-[200px] h-10 md:h-12 flex items-center justify-center font-semibold bg-[#FAC000] text-white rounded-lg px-4 md:px-6 transition duration-300 hover:bg-black hover:text-white mx-auto md:mx-0">
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
                            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-full h-48 object-contain">

                            <!-- Blur Overlay on Hover -->
                            <div class="absolute inset-0 backdrop-blur-sm bg-white/30 opacity-0 group-hover/item:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                                <h3 class="text-black text-lg font-extrabold text-center px-2">{{ $product->name }}</h3>
                            </div>
                        </div>
                    @endforeach

                    <!-- Duplicate for seamless looping -->
                    @foreach($products as $product)
                        <div class="min-w-[250px] max-w-[250px] h-[250px] bg-white border rounded-lg shadow-lg overflow-hidden relative group/item hover:scale-105 transition-transform duration-300">
                            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-full h-48 object-contain">

                            <div class="absolute inset-0 backdrop-blur-sm bg-white/30 opacity-0 group-hover/item:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                                <h3 class="text-black text-lg font-extrabold text-center px-2">{{ $product->name }}</h3>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>



</x-layout>