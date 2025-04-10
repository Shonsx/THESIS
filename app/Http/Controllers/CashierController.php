<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Notifications\OrderProcessed;
use App\Models\CustomNotification;
use Illuminate\Support\Str;

class CashierController extends Controller
{
    public function updateStatus($orderId)
    {
        // Fetch the single order by its ID
        $order = Order::findOrFail($orderId);

        // Check if it's a single order instance
        if ($order instanceof Order) {
            // Mark as processed
            $order->update(['processed' => true]);

            // Notify the user (pass the single $order instance)
            $order->user->notify(new OrderProcessed($order, 'Processing'));

            return redirect()->back()->with('success', 'Order is now being processed!');
        }

        // If it's not an Order instance (very unlikely if you're using findOrFail)
        return redirect()->back()->with('error', 'Order not found.');
    }




    public function completeOrder(Order $order)
    {
        // Ensure the order is an instance, not a collection
        if (!$order instanceof Order) {
            abort(404, 'Order not found');
        }

        // Mark the order as completed
        $order->processed = true;
        $order->save();

        // Define the status to "Completed"
        $status = 'Completed';

        // Send a notification when the order is completed
        $order->user->notify(new OrderProcessed($order, $status)); // Pass both order and status

        // Store the history in notifications table
        CustomNotification::create([
            'notifiable_type' => $order->user ? get_class($order->user) : 'Unknown',
            'notifiable_id' => $order->user ? $order->user->id : null,
            'message' => 'Your order for ' . ($order->product ? $order->product->name : 'Unknown Product') . ' has been completed.',
            'order_id' => $order->id,
        ]);
        
        

        return redirect()->route('cashier.history')->with('success', 'Order completed!');
    }

    public function orderDetails($orderId)
    {
        $order = Order::findOrFail($orderId);
        return view('cashier.buyerDetails', compact('order'));
    }


    public function history()
    {
        // Fetch all processed orders and sort by newest first
        $processedOrders = Order::where('processed', true)
                                ->orderBy('updated_at', 'desc')
                                ->get();

        return view('cashier.history', compact('processedOrders'));
    }


}
