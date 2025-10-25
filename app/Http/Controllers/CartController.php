<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Cart;
use App\Models\Product;
use App\Models\Order;
use App\Models\GCash;
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
    public function show(Request $request)
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable $user */
        $user = Auth::user();

        // Get current page for each section or default to 1
        $cartPage = $request->get('cart_page', 1);
        $orderPage = $request->get('order_page', 1);

        // Paginate cart items separately for cart
        $cartItems = Cart::where('user_id', $user->id)->paginate(3, ['*'], 'cart_page', $cartPage);

        // Paginate order history separately for orders
        $userOrders = Order::where('user_id', $user->id)
                            ->with('product')
                            ->latest()
                            ->paginate(4, ['*'], 'order_page', $orderPage);

        return view('etry.cart', compact('cartItems', 'userOrders'));
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
        $gcash = Gcash::latest()->first();

        // For single product checkout
        if ($productId) {
            $product = Product::with('stocks')->findOrFail($productId);

            return view('etry.checkout', [
                'product' => $product,
                'gcash' => $gcash,
            ]);
        }

        // For cart checkout
        $cartItems = Cart::with('product.stocks')
            ->whereIn('id', $selectedItems)
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.show')->with('error', 'No items selected for checkout.');
        }

        return view('etry.checkout', compact('cartItems', 'gcash'));
    }

}
