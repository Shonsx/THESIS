<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Cart;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\ProductStock;

class ProductController extends Controller
{
    public function create() {
        return view('etry.addProduct');
    }

    public function index(Request $request)
    {
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

        return view('etry.indexAdmin', compact('products', 'sortOption', 'genderFilter', 'cartItemIds'));
    }




    public function update(Request $request, $productId)
    {
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
                $stockQuantity = $request->input('stock.' . $size, 0);  // Get stock for the size, default 0 if not provided

                if ($stockQuantity > 0) {
                    // Check if stock record already exists for this size
                    $productStock = ProductStock::where('product_id', $product->id)->where('size', $size)->first();

                    if ($productStock) {
                        // If stock record exists, add the new stock quantity to the existing stock
                        $productStock->stock += $stockQuantity;
                        $productStock->save();
                    } else {
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

            // Decode existing sizes
            $existingSizes = $product->sizes ? json_decode($product->sizes, true) : [];

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
            // Delete the old image if it exists
            if ($product->image && file_exists(public_path('storage/' . $product->image))) {
                unlink(public_path('storage/' . $product->image)); // Delete the old image
            }

            // Upload the new image
            $imagePath = $request->file('image')->store('products', 'public'); // Store in the 'public' disk
            $product->image = $imagePath; // Update the product's image path
            $product->save();
        }

        return redirect()->route('products.index')->with('success', 'Product updated successfully!');
    }


    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'price' => 'required|numeric',
            'description' => 'nullable|string',
            'image' => 'image|nullable',
            'measurement_image' => 'image|nullable',
            'sizes' => 'array',
            'gender' => 'required|in:Men,Women',
        ]);

        // Handle product image
        $imagePath = $request->file('image')?->store('products', 'public');

        $measurementImagePath = null;
        if ($request->hasFile('measurement_image')) {
            $file = $request->file('measurement_image');
            $filename = $file->getClientOriginalName(); // keep original name
            $storagePath = 'measurements/' . $filename;

            // Check if the file already exists
            if (!Storage::disk('public')->exists($storagePath)) {
                // If not exists, store it
                $file->storeAs('measurements', $filename, 'public');
            }

            // Either way, set the path
            $measurementImagePath = $storagePath;
        }

        // Save product
        $product = Product::create([
            'name' => $request->name,
            'price' => $request->price,
            'description' => $request->description,
            'image' => $imagePath,
            'measurement_image' => $measurementImagePath, // ← Save measurement image
            'sizes' => json_encode($request->sizes),
            'gender' => $request->gender,
        ]);

        // Save size/stock in ProductStock table
        foreach ($request->sizes as $size) {
            $stock = $request->stock[$size] ?? 0;

            ProductStock::create([
                'product_id' => $product->id,
                'size' => $size,
                'stock' => $stock,
            ]);
        }

        return redirect()->route('products.index')->with('success', 'Product added!');
    }




    public function show($id){
        $product = Product::findOrFail($id);
        $stocks = ProductStock::where('product_id', $id)->get();
        $user = Auth::user();
        $cartItemIds = $user 
            ? Cart::where('user_id', $user->id)->pluck('product_id')->toArray() 
            : [];
        return view('etry.product-details', compact('product', 'stocks', 'cartItemIds'));
    }


    public function destroy($id)
    {
        if (auth::user()->id !== 1) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $product = Product::findOrFail($id);

        // Delete image if needed
        if ($product->image) {
            Storage::delete('public/' . $product->image);
        }

        $product->delete();
        
        return redirect()->route('products.indexAdmin')
                     ->with('success', 'Product deleted successfully.');
    }

}
