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
    
        <!-- Empty Container for Cards BELOW the "Featured Products" -->
        <div class="max-w-[1200px] w-full h-auto mt-5 p-5 flex flex-wrap justify-center">
            <!-- Cards will go here -->
            <div class="container mx-auto">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                    <!-- Product Card 1 -->
                    <div class="border rounded-lg overflow-hidden relative bg-white transition-transform duration-300 hover:shadow-xl hover:-translate-y-2">
                        <img src="" alt="Black Jacket" class="w-full h-64 object-contain">
                        <div class="p-4 flex justify-between items-center">
                            <div>
                                <h2 class="text-lg font-semibold">Black Jacket</h2>
                                <p class="text-xl font-bold text-black">₱154.99</p>
                            </div>
                            <a href="{{ url('/virtual-tryon') }}">
                                <img src="{{ asset('icons/camera-svgrepo-com.svg') }}" alt="Camera Icon" class="w-8 h-8">
                            </a>
                        </div>
                    </div>
        
                    <!-- Product Card 2 -->
                    <div class="border rounded-lg overflow-hidden relative bg-white transition-transform duration-300 hover:shadow-xl hover:-translate-y-2">
                        <img src="" alt="Mom Jeans" class="w-full h-64 object-cover">
                        <div class="p-4 flex justify-between items-center">
                            <div>
                                <h2 class="text-lg font-semibold">Mom Jeans</h2>
                                <p class="text-xl font-bold text-black">₱154.99</p>
                            </div>
                            <a href="{{ url('/virtual-tryon') }}">
                                <img src="{{ asset('icons/camera-svgrepo-com.svg') }}" alt="Camera Icon" class="w-8 h-8">
                            </a>
                        </div>
                    </div>
        
                    <!-- Product Card 3 -->
                    <div class="border rounded-lg overflow-hidden relative bg-white transition-transform duration-300 hover:shadow-xl hover:-translate-y-2">
                        <img src="" alt="Blank Olive T-shirt" class="w-full h-64 object-cover">
                        <div class="p-4 flex justify-between items-center">
                            <div>
                                <h2 class="text-lg font-semibold">Blank Olive T-shirt</h2>
                                <p class="text-xl font-bold text-black">₱119.99</p>
                            </div>
                            <a href="{{ url('/virtual-tryon') }}">
                                <img src="{{ asset('icons/camera-svgrepo-com.svg') }}" alt="Camera Icon" class="w-8 h-8">
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
    
    
    
    

</x-layout>