<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Cart;
use App\Models\Product;

class CartController extends Controller
{
    public function addToCart($id){
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = Auth::user();

        // Check if the product is already in the cart
        $cartItem = Cart::where('user_id', $user->id)
                        ->where('product_id', $id)
                        ->first();

        if ($cartItem) {
            // Remove from cart if it exists
            $cartItem->delete();
            $added = false;
        } else {
            // Add to cart if not yet added
            Cart::create([
                'user_id' => $user->id,
                'product_id' => $id,
                'quantity' => 1
            ]);
            $added = true;
        }

        return response()->json([
            'success' => true,
            'added' => $added
        ]);
    }


    // Display the cart view
    public function show(){
        /** @var \Illuminate\Contracts\Auth\Authenticatable $user */
        $user = Auth::user();
        $cartItems = Cart::where('user_id', $user->id)->get();
        return view('etry.cart', compact('cartItems'));
    }

    public function bulkAction(Request $request){
        $selectedItems = $request->input('selected_items', []);
        $action = $request->input('action');

        if ($action === 'remove') {
            Cart::whereIn('id', $selectedItems)->delete();
            return redirect()->route('cart.show')->with('success', 'Selected items removed.');
        }

        if ($action === 'buy') {
            return redirect()->route('checkout.index', ['items' => implode(',', $selectedItems)]);
        }
        

        return redirect()->route('cart.show')->with('error', 'Invalid action.');
    }

    public function checkout(Request $request){
        $selectedItems = explode(',', $request->query('items', ''));
        $productId = $request->query('productId'); // NEW: for direct product purchase

        // For single product checkout
        if ($productId) {
            $product = Product::with('stocks')->findOrFail($productId);

            return view('etry.checkout', [
                'product' => $product,
            ]);
        }

        // For cart checkout
        $cartItems = Cart::with('product.stocks')
            ->whereIn('id', $selectedItems)
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.show')->with('error', 'No items selected for checkout.');
        }

        return view('etry.checkout', compact('cartItems'));
    }

}
