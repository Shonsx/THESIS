<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ProductStock;
use App\Models\Product;
use App\Models\Order;
use App\Models\Cart;
use App\Models\User;
use App\Models\Address;
use App\Notifications\NewOrderPlaced;

class CheckoutController extends Controller
{
    public function process(Request $request)
    {
        $user = Auth::user();
        $orders = [];

        // ✅ Validate and Save Shipping Address
        $validated = $request->validate([
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'zip' => 'required|string|max:20',
        ]);

        Address::updateOrCreate(
            ['user_id' => $user->id],
            [
                'address' => $validated['address'],
                'city' => $validated['city'],
                'state' => $validated['state'],
                'zip' => $validated['zip'],
            ]
        );

        // 🛍️ Process single item checkout
        if ($request->type === 'single') {
            $product = Product::findOrFail($request->product_id);
            $stock = ProductStock::where('product_id', $product->id)
                                 ->where('size', $request->size)
                                 ->first();

            if ($stock && $stock->stock >= $request->quantity) {
                $stock->decrement('stock', $request->quantity);

                $order = Order::create([
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                    'size' => $request->size,
                    'quantity' => $request->quantity,
                    'total_price' => $product->price * $request->quantity,
                ]);

                $orders[] = $order;
            }

        // 🛒 Process cart checkout
        } elseif ($request->type === 'cart') {
            $cartItems = Cart::where('user_id', $user->id)->get();

            foreach ($cartItems as $item) {
                $size = $request->input("items.{$item->id}.size");
                $quantity = $request->input("items.{$item->id}.quantity");

                $stock = ProductStock::where('product_id', $item->product_id)
                                     ->where('size', $size)
                                     ->first();

                if ($stock && $stock->stock >= $quantity) {
                    $stock->decrement('stock', $quantity);

                    $order = Order::create([
                        'user_id' => $user->id,
                        'product_id' => $item->product_id,
                        'size' => $size,
                        'quantity' => $quantity,
                        'total_price' => $item->product->price * $quantity,
                    ]);

                    $orders[] = $order;
                }
            }

            Cart::where('user_id', $user->id)->delete();
        }

        // 🔔 Notify all cashiers and admins
        if (!empty($orders)) {
            $cashiers = User::whereIn('role', ['cashier', 'admin'])->get();

            foreach ($cashiers as $cashier) {
                foreach ($orders as $order) {
                    $cashier->notify(new NewOrderPlaced($order));
                }
            }
        }

        return redirect()->route('cashier.main')->with('success', 'Checkout successful!');
    }
}
