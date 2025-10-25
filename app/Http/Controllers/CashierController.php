<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Notifications\OrderProcessed;
use App\Notifications\NewOrderPlaced;
use App\Notifications\OrderRejected;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CashierController extends Controller
{
    public function updateStatus($orderId)
    {
        $order = Order::findOrFail($orderId);

        if ($order instanceof Order) {
            $order->update(['processed' => true]);
            $order->user->notify(new OrderProcessed($order, 'Processing'));
            return redirect()->back()->with('success', 'Order is now being processed!');
        }

        return redirect()->back()->with('error', 'Order not found.');
    }

    public function completeOrder(Order $order)
    {
        if (!$order instanceof Order) {
            abort(404, 'Order not found');
        }

        $order->processed = true;
        $order->save();

        $status = 'Completed';
        $order->user->notify(new OrderProcessed($order, $status));

        return redirect()->route('cashier.main')->with('success', 'Order completed!');
    }

    public function orderDetails($id)
    {
        $order = Order::with(['user', 'product'])->findOrFail($id);
        return view('cashier.buyerDetails', compact('order'));
    }

    public function history()
    {
        $processedOrders = Order::where('processed', true)
                                ->orderBy('updated_at', 'desc')
                                ->get();

        return view('cashier.history', compact('processedOrders'));
    }

    // New: Reject order with reason; sends notification to the user
    public function rejectOrder(Request $request, $orderId)
    {
        $user = Auth::user();
        if (!$user || !in_array($user->role, ['admin', 'manager'])) {
            return redirect()->back()->with('error', 'Unauthorized');
        }

        $order = Order::findOrFail($orderId);
        $validated = $request->validate([
            'reason' => 'required|string|max:1000'
        ]);

        // Remove payment proof file and unset path so it leaves manager pending list
        if ($order->payment_proof_path) {
            if (Storage::exists($order->payment_proof_path)) {
                Storage::delete($order->payment_proof_path);
            }
            $order->payment_proof_path = null;
        }

        $order->processed = false; // remains unprocessed, back to user
        $order->save();

        // Notify the customer with rejection reason and link to details page
        $order->user->notify(new OrderRejected($order, $validated['reason']));

        // Notify other admins/managers as well (exclude the acting user to avoid redundant self-notification)
        $staffRecipients = \App\Models\User::whereIn('role', ['admin', 'manager'])
            ->where('id', '!=', $user->id)
            ->get();
        foreach ($staffRecipients as $recipient) {
            $recipient->notify(new OrderRejected($order, $validated['reason']));
        }

        return redirect()->route('cashier.main')
            ->with('success', 'Order rejected and returned to customer.');
    }

    // New: Show rejection details page for the customer
    public function rejectionView(Order $order)
    {
        // Ensure the authenticated user is the owner of the order or an admin/manager
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }
        if ($user->id !== $order->user_id && !in_array($user->role, ['admin', 'manager'])) {
            return redirect()->route('products.index')->with('error', 'Access denied');
        }

        // Get the latest rejection notification for this order to display the reason
        $reason = null;
        $rejectionNotification = $user->notifications
            ->filter(function ($n) use ($order) {
                return $n->type === \App\Notifications\OrderRejected::class
                    && isset($n->data['order_id'])
                    && (int)$n->data['order_id'] === (int)$order->id;
            })
            ->sortByDesc('created_at')
            ->first();
        if ($rejectionNotification) {
            $reason = $rejectionNotification->data['reason'] ?? null;
        }

        return view('etry.rejection', compact('order', 'reason'));
    }
}
