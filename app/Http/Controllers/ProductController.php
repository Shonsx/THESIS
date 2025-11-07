<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Cart;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\ProductStock;
use App\Models\SiteVisit;
use App\Models\Order;

    class ProductController extends Controller
    {
    public function create() {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized');
        }
        return view('etry.addProduct');
    }

    public function index(Request $request)
    {
        // Log visit for analytics
        try {
            $this->logVisit($request);
        } catch (\Throwable $e) {
            // Silently ignore logging errors to not disrupt page
        }

        $sortOption = $request->get('sort', 'desc');
        $genderFilter = $request->get('gender', ''); // Get gender filter from request
        $searchTerm = $request->get('search', ''); // Get search query from request

        $products = Product::when($searchTerm, function ($query, $searchTerm) {
                return $query->where('name', 'like', '%' . $searchTerm . '%');
            })
            ->when($sortOption === 'price_asc', function ($query) {
                return $query->orderBy('price', 'asc');
            })
            ->when($sortOption === 'price_desc', function ($query) {
                return $query->orderBy('price', 'desc');
            })
            ->when($sortOption === 'asc' || $sortOption === 'desc', function ($query) use ($sortOption) {
                return $query->orderBy('created_at', $sortOption);
            })
            ->when(!empty($genderFilter), function ($query) use ($genderFilter) {
                return $query->where('gender', $genderFilter);
            })
            ->paginate(12);

        $user = Auth::user();
        $cartItemIds = $user 
            ? Cart::where('user_id', $user->id)->pluck('product_id')->toArray() 
            : [];

        return view('etry.index', compact('products', 'sortOption', 'cartItemIds', 'genderFilter', 'searchTerm'));
    }

    public function indexAdmin(Request $request)
    {
        // Ensure only admin can access
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized');
        }

        $sortOption = $request->get('sort', 'desc'); // default sort
        $genderFilter = $request->get('gender', '');

        // Fetch all products with sorting and filtering
        $products = Product::when($genderFilter, function($query, $genderFilter) {
                return $query->where('gender', $genderFilter);
            })
            ->when($sortOption === 'price_asc', function ($query) {
                return $query->orderBy('price', 'asc');
            })
            ->when($sortOption === 'price_desc', function ($query) {
                return $query->orderBy('price', 'desc');
            })
            ->when($sortOption === 'asc' || $sortOption === 'desc', function ($query) use ($sortOption) {
                return $query->orderBy('created_at', $sortOption);
            })
            ->paginate(12);

        $user = Auth::user();
        $cartItemIds = $user 
            ? Cart::where('user_id', $user->id)->pluck('product_id')->toArray() 
            : [];

        // Analytics: current online users and daily unique visitors + history
        $currentOnline = SiteVisit::where('last_seen', '>=', now()->subMinutes(5))->count();
        $todayVisitors = SiteVisit::whereDate('visited_at', now())->count();
        $dailyHistory = SiteVisit::selectRaw('visited_at, COUNT(*) as count')
            ->groupBy('visited_at')
            ->orderBy('visited_at', 'desc')
            ->limit(30)
            ->get();

        // Content interactions (Buy Now clicks)
        $todayInteractions = \App\Models\ContentInteraction::whereDate('created_at', now())->count();
        $totalInteractions = \App\Models\ContentInteraction::count();
        $dailyInteractionHistory = \App\Models\ContentInteraction::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->limit(30)
            ->get();

        // Sales analysis: processed orders totals
        $todayProcessedSales = Order::where('processed', true)
            ->whereDate('updated_at', now())
            ->sum('total_price');
        $dailySalesHistory = Order::selectRaw('DATE(updated_at) as date, SUM(total_price) as total')
            ->where('processed', true)
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->limit(30)
            ->get();

        return view('etry.indexAdmin', compact('products', 'sortOption', 'genderFilter', 'cartItemIds', 'currentOnline', 'todayVisitors', 'dailyHistory', 'todayProcessedSales', 'dailySalesHistory', 'todayInteractions', 'totalInteractions', 'dailyInteractionHistory'));
    }

    public function indexManager(Request $request)
    {
        // Ensure only manager can access
        if (!Auth::check() || Auth::user()->role !== 'manager') {
            abort(403, 'Unauthorized');
        }

        $sortOption = $request->get('sort', 'desc');
        $genderFilter = $request->get('gender', '');

        $products = Product::when($genderFilter, function($query, $genderFilter) {
                return $query->where('gender', $genderFilter);
            })
            ->when($sortOption === 'price_asc', function ($query) {
                return $query->orderBy('price', 'asc');
            })
            ->when($sortOption === 'price_desc', function ($query) {
                return $query->orderBy('price', 'desc');
            })
            ->when($sortOption === 'asc' || $sortOption === 'desc', function ($query) use ($sortOption) {
                return $query->orderBy('created_at', $sortOption);
            })
            ->paginate(12);

        $user = Auth::user();
        $cartItemIds = $user 
            ? Cart::where('user_id', $user->id)->pluck('product_id')->toArray() 
            : [];

        return view('etry.indexManager', compact('products', 'sortOption', 'genderFilter', 'cartItemIds'));
    }




    public function update(Request $request, $productId)
    {
        if (!Auth::check() || !in_array(Auth::user()->role, ['admin','manager'])) {
            abort(403, 'Unauthorized');
        }
        $product = Product::findOrFail($productId);
        // Validate and update product details (name, description, price)
        $product->update([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'price' => $request->input('price'),
        ]);

        // Handle the new sizes and stock updates
        if ($request->has('sizes') && is_array($request->sizes)) {
            foreach ($request->sizes as $size) {
                $stockQuantity = (int) $request->input('stock.' . $size, 0);  // positive to increase, negative to decrease

                if ($stockQuantity !== 0) {
                    // Check if stock record already exists for this size
                    $productStock = ProductStock::where('product_id', $product->id)
                        ->where('size', $size)
                        ->first();

                    if ($productStock) {
                        // Adjust stock and clamp at 0
                        $newStock = $productStock->stock + $stockQuantity;
                        $productStock->stock = max(0, $newStock);
                        $productStock->save();
                    } else if ($stockQuantity > 0) {
                        // Create record only when increasing stock for a new size
                        ProductStock::create([
                            'product_id' => $product->id,
                            'size' => $size,
                            'stock' => $stockQuantity,
                        ]);
                    }
                }
            }
            // Define the desired order of sizes
            $sizeOrder = ['', 'S', 'M', 'L', 'XL'];

            // Decode existing sizes safely
            $existingSizesRaw = $product->sizes;
            $existingSizes = [];
            if (is_string($existingSizesRaw)) {
                $existingSizes = json_decode($existingSizesRaw, true) ?: [];
            } elseif (is_array($existingSizesRaw)) {
                $existingSizes = $existingSizesRaw; // already array
            } elseif (!empty($existingSizesRaw)) {
                $existingSizes = [];
            }

            // Merge and get unique list
            $updatedSizes = array_unique(array_merge($existingSizes, $request->sizes));

            // Sort sizes according to defined order
            usort($updatedSizes, function ($a, $b) use ($sizeOrder) {
                return array_search($a, $sizeOrder) <=> array_search($b, $sizeOrder);
            });

            // Save updated, sorted sizes
            $product->sizes = json_encode($updatedSizes);
            $product->save();

        }

        // Handle the image update
         if ($request->hasFile('image')) {
             // Delete the old image if it exists under public/images
             if ($product->image && \Illuminate\Support\Facades\Storage::disk('public')->exists($product->image)) {
                 \Illuminate\Support\Facades\Storage::disk('public')->delete($product->image);
             }

             // Upload the new image directly to public/images/products
             $imagePath = $request->file('image')->store('products', 'public');
             $product->image = $imagePath;
             $product->save();
         }

         // Handle measurement image update
         if ($request->hasFile('measurement_image')) {
             // Delete the old measurement image under public/images
             if ($product->measurement_image && \Illuminate\Support\Facades\Storage::disk('public')->exists($product->measurement_image)) {
                 \Illuminate\Support\Facades\Storage::disk('public')->delete($product->measurement_image);
             }
             $file = $request->file('measurement_image');
             $filename = $file->getClientOriginalName();
             $storagePath = 'measurements/' . $filename;
             if (!\Illuminate\Support\Facades\Storage::disk('public')->exists($storagePath)) {
                 $file->storeAs('measurements', $filename, 'public');
             }
             $product->measurement_image = $storagePath;
             $product->save();
         }

         return redirect()->route(Auth::user()->role === 'manager' ? 'manager.index' : 'admin.index')->with('success', 'Product updated successfully!');
    }


    public function store(Request $request)
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized');
        }
        $request->validate([
            'name' => 'required|string',
            'price' => 'required|numeric',
            'description' => 'nullable|string',
            'image' => 'image|nullable',
            'extra_images.*' => 'image|nullable',
            'measurement_image' => 'image|nullable',
            'sizes' => 'array|nullable',
            'gender' => 'required|in:Men,Women',
        ]);

        // Handle product image (store under public storage if provided)
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imageFile = $request->file('image');
            if ($imageFile->isValid()) {
                $imagePath = $imageFile->store('products', 'public');
            } else {
                Log::warning('Product create: invalid image upload');
            }
        }

        $measurementImagePath = null;
        if ($request->hasFile('measurement_image')) {
            $file = $request->file('measurement_image');
            $filename = $file->getClientOriginalName(); // keep original name
            $storagePath = 'measurements/' . $filename;

            // Check if the file already exists
            if (!Storage::disk('public')->exists($storagePath)) {
                // If not exists, store it under public/images
                $file->storeAs('measurements', $filename, 'public');
            }

            // Either way, set the path
            $measurementImagePath = $storagePath;
        }

        // Handle extra images (multiple)
        $extraImagePaths = [];
        if ($request->hasFile('extra_images')) {
            foreach ((array)$request->file('extra_images') as $extraFile) {
                if ($extraFile && $extraFile->isValid()) {
                    $extraImagePaths[] = $extraFile->store('products', 'public');
                }
            }
        }

        // Save product
        $sizes = $request->input('sizes', []);
        if (!is_array($sizes)) { $sizes = []; }

        $product = Product::create([
            'name' => $request->name,
            'price' => $request->price,
            'description' => $request->description,
            'image' => $imagePath ?? '',
            'extra_images' => $extraImagePaths,
            'measurement_image' => $measurementImagePath, // ← Save measurement image
            'sizes' => json_encode($sizes),
            'gender' => $request->gender,
        ]);

        // Save size/stock in ProductStock table
        foreach ($sizes as $size) {
            $stock = $request->stock[$size] ?? 0;

            ProductStock::create([
                'product_id' => $product->id,
                'size' => $size,
                'stock' => $stock,
            ]);
        }

        return redirect()->route('admin.index')->with('success', 'Product added!');
    }




    public function show($id){
        $product = Product::findOrFail($id);
        $stocks = ProductStock::where('product_id', $id)->get();
        $user = Auth::user();
        $cartItemIds = $user 
            ? Cart::where('user_id', $user->id)->pluck('product_id')->toArray() 
            : [];
        // Reviews & Ratings
        $averageRating = round((float) ($product->reviews()->avg('rating') ?? 0), 1);
        $reviews = $product->reviews()->with('user')->latest()->paginate(5);
        $reviewsCount = $product->reviews()->count();

        return view('etry.product-details', compact('product', 'stocks', 'cartItemIds', 'averageRating', 'reviews', 'reviewsCount'));
    }


    public function destroy($id)
    {
        if (!Auth::check() || !in_array(Auth::user()->role, ['admin','manager'])) {
            return redirect()->back()->with('error', 'Unauthorized');
        }
        $product = Product::findOrFail($id);

        // Delete image if needed
        if ($product->image) {
            if (Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }
        }

        $product->delete();
        
        return redirect()->route(Auth::user()->role === 'manager' ? 'manager.index' : 'admin.index')
                     ->with('success', 'Product deleted successfully.');
    }

    public function adjustStock(Request $request, Product $product)
    {
        if (!Auth::check() || !in_array(Auth::user()->role, ['admin','manager'])) {
            abort(403, 'Unauthorized');
        }
        $data = $request->validate([
            'size' => 'required|in:S,M,L,XL',
            'amount' => 'required|integer|min:1',
        ]);
        $stock = ProductStock::where('product_id', $product->id)
            ->where('size', $data['size'])
            ->first();
        if (!$stock) {
            return back()->with('error', 'No stock record found for this size.');
        }
        $decrement = min($data['amount'], $stock->stock);
        if ($decrement <= 0) {
            return back()->with('error', 'Stock is already 0 for selected size.');
        }
        $stock->decrement('stock', $decrement);
        return back()->with('success', "Stock decreased by {$decrement} for size {$data['size']}.");
    }

    private function logVisit(Request $request): void
    {
        $user = Auth::user();
        $identifier = [
            'visited_at' => now()->toDateString(),
            'user_id' => $user?->id,
            'ip_address' => $user ? null : $request->ip(),
        ];

        SiteVisit::updateOrCreate($identifier, [
            'user_agent' => $request->userAgent(),
            'last_seen' => now(),
        ]);
    }

    // --- Real-time analytics endpoints ---
    public function heartbeat(Request $request)
    {
        $user = Auth::user();
        $today = now()->toDateString();

        // Upsert current visitor record
        $identifier = [
            'visited_at' => $today,
            'user_id' => $user?->id,
            'ip_address' => $user ? null : $request->ip(),
        ];

        SiteVisit::updateOrCreate($identifier, [
            'user_agent' => $request->userAgent(),
            'last_seen' => now(),
        ]);

        // If logged in, merge any guest record for same IP today to avoid duplicates
        if ($user) {
            $guest = SiteVisit::where('visited_at', $today)
                ->whereNull('user_id')
                ->where('ip_address', $request->ip())
                ->first();
            $userRecord = SiteVisit::where('visited_at', $today)
                ->where('user_id', $user->id)
                ->first();
            if ($guest && $userRecord) {
                // Remove guest entry to prevent double counting
                $guest->delete();
            } elseif ($guest && !$userRecord) {
                // Promote guest entry to user entry
                $guest->user_id = $user->id;
                $guest->ip_address = null;
                $guest->save();
            }
        }

        return response()->json(['ok' => true]);
    }

    public function onlineCount()
    {
        $threshold = now()->subMinutes(5);
        $visitors = SiteVisit::where('last_seen', '>=', $threshold)
            ->get(['user_id', 'ip_address']);
        $uniqueCount = collect($visitors)
            ->map(function ($v) {
                return $v->user_id ? ('U:' . $v->user_id) : ('I:' . $v->ip_address);
            })
            ->unique()
            ->count();

        return response()->json(['count' => $uniqueCount]);
    }
}
