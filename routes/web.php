<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\InteractionController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CashierController;
use App\Http\Controllers\NotificationController;
use App\Models\Order;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\GCashController;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;

Route::get('/', [HomeController::class, 'index'])->name('welcome');
Route::get('/admin', [ProductController::class, 'indexAdmin'])
    ->name('admin.index')
    ->middleware('auth');
Route::get('/manager', [ProductController::class, 'indexManager'])
    ->name('manager.index')
    ->middleware('auth');

Route::get('/product', [ProductController::class, 'index'])->name('products.index');

Route::get('/login', function () {
    return view('etry.login');
});

Route::get('/signup', function () {
    return view('etry.signup');
});

// -- AR SYSTEM ROUTING --




Route::post('/register', [AuthController::class,'register'])->name('register');
Route::post('/login', [AuthController::class,'login'])->name('login');
Route::post('/admin/login', [AuthController::class,'adminLogin'])->name('admin.login');
Route::get('/admin/first-login', [AuthController::class,'showFirstLogin'])->name('admin.first-login')->middleware('auth');
Route::post('/admin/update-profile', [AuthController::class,'updateAdminProfile'])->name('admin.update-profile')->middleware('auth');
Route::post('logout', [AuthController::class,'logout'])->name('logout');

// User forgot password flow
Route::middleware('guest')->group(function () {
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

// Admin forgot password (phone verified)
Route::middleware('guest')->group(function () {
    Route::get('/admin/forgot-password', [AuthController::class, 'showAdminForgotPassword'])->name('admin.password.request');
    Route::post('/admin/forgot-password', [AuthController::class, 'resetAdminPassword'])->name('admin.password.update');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/addProduct', [ProductController::class, 'create'])->name('addProduct');
    Route::post('/addProduct', [ProductController::class, 'store'])->name('addProduct.store');
});
Route::get('/products/{id}', [ProductController::class, 'show'])->name('products.show');
// Reviews
Route::middleware(['auth'])->group(function () {
    Route::get('/products/{product}/reviews/create', [ReviewController::class, 'create'])->name('reviews.create');
    Route::post('/products/{product}/reviews', [ReviewController::class, 'store'])->name('reviews.store');
});
Route::delete('/products/delete/{id}', [ProductController::class, 'destroy'])->middleware('auth');
Route::put('/products/{id}', [ProductController::class, 'update'])->name('products.update');
Route::post('/products/{product}/adjust-stock', [ProductController::class, 'adjustStock'])
    ->middleware('auth')
    ->name('products.adjustStock');
// GCash feature removed: using static public image instead

// Serve files from storage/app/public without relying on web server symlink
// More tolerant: accepts paths like "products/foo.webp", "storage/products/foo.webp", or just "foo.webp"
Route::get('/files/{path}', function (string $path) {
    $root = storage_path('app/public');
    $path = ltrim($path, '/');

    // Helper to resolve a candidate under public storage
    $resolve = function (string $candidate) use ($root) {
        $full = $root . '/' . $candidate;
        if (!\Illuminate\Support\Facades\File::exists($full)) {
            return null;
        }
        $real = realpath($full);
        if (!$real || strpos($real, $root) !== 0) {
            return null; // prevent path traversal
        }
        return $real;
    };

    // Try exact path first
    $full = $resolve($path);

    // Strip common wrong prefixes (e.g., "storage/")
    if (!$full) {
        $stripped = preg_replace('#^(?:storage/)+#', '', $path);
        if ($stripped !== $path) {
            $full = $resolve($stripped);
        }
    }

    // Try buckets with basename fallback
    if (!$full) {
        $name = basename($path);
        foreach (['products', 'measurements', 'gcash', 'payment_proofs'] as $bucket) {
            $full = $resolve($bucket . '/' . $name);
            if ($full) break;
        }
    }

    if (!$full) {
        abort(404);
    }

    $mime = \Illuminate\Support\Facades\File::mimeType($full) ?: 'application/octet-stream';
    return response()->file($full, ['Content-Type' => $mime]);
})->where('path', '.*')->name('files.public');



// ACCOUNT EDIT
Route::middleware('auth')->group(function () {
    Route::get('/account', [AccountController::class, 'showAccount'])->name('account');
    Route::get('/edit-account', [AccountController::class, 'showEditAccount'])->name('editAccount');
    Route::post('/update-account', [AccountController::class, 'updateAccount'])->name('updateAccount');
    Route::delete('/delete-account', [AccountController::class, 'deleteAccount'])->name('delete-account');
});
// ANOTHER ACCOUNT EDIT (ADDRESSES)
Route::middleware(['auth'])->group(function () {
    // Route to view address settings
    Route::get('account/settings/address', [AccountController::class, 'showAddressSettings'])->name('account.settings.address');
    
    // Route to handle the form submission for updating address
    Route::post('account/settings/address', [AccountController::class, 'updateAddress'])->name('account.updateAddress');
});



// SETTINGS FOR ADMIN
Route::get('/settings/admin', function () {
    return view('etry.settingsAdmin');
})->name('settings.admin');
// SETTINGS FOR USERS
Route::get('/settings/account', function () {
    return view('etry.settingsAccounts');
})->name('settings.account');

// CASHIER ROUTE
// Consolidated below under CASHIER CONTROL with manager-only access


// ADMIN CONTROLLER
Route::middleware(['auth'])->group(function () {
    Route::get('/settings/admin', [AdminController::class, 'settings'])->name('settings.admin');
    Route::post('/update-role/{id}', [AccountController::class, 'updateUserRole'])->name('update-role');
});

Route::middleware(['auth'])->group(function () {
    Route::post('/cart/add/{id}', [CartController::class, 'addToCart'])->name('cart.add');
    Route::get('/cart', [CartController::class, 'show'])->name('cart.show');
    Route::post('/cart/bulk-action', [CartController::class, 'bulkAction'])->name('cart.bulkAction');
    Route::get('/checkout', [CartController::class, 'checkout'])->name('checkout.index');
});

// Lightweight real-time counters for badges
Route::middleware(['auth'])->group(function () {
    Route::get('/notifications/count', function () {
        $user = \Illuminate\Support\Facades\Auth::user();
        $unread = $user ? ($user->unreadNotifications ? $user->unreadNotifications->count() : 0) : 0;
        return response()->json(['unread' => $unread]);
    })->name('notifications.count');

    Route::get('/cart/count', function () {
        $userId = \Illuminate\Support\Facades\Auth::id();
        $count = $userId ? \App\Models\Cart::where('user_id', $userId)->count() : 0;
        return response()->json(['count' => $count]);
    })->name('cart.count');
});

//CASHIER CONTROL
Route::middleware(['auth'])->group(function() {
    Route::get('/cashier', function () {
        if (Auth::user()->role !== 'manager') {
            return redirect()->route('products.index')->with('error', 'Access denied.');
        }
        $orders = \App\Models\Order::where('processed', false)
            ->whereNotNull('payment_proof_path')
            ->latest()
            ->paginate(20);
        return view('cashier.main', compact('orders'));
    })->name('cashier.main');

    Route::patch('/cashier/complete/{id}', function ($id) {
        $order = Order::findOrFail($id);
        $order->update(['processed' => true]);
        return redirect()->route('cashier.main')->with('success', 'Order completed!');
    })->name('cashier.complete');

    Route::post('/checkout/process', [CheckoutController::class, 'process'])->name('checkout.process');
    Route::get('/cashier/history', [CashierController::class, 'history'])->name('cashier.history');
    Route::patch('/cashier/update-status/{order}', [CashierController::class, 'updateStatus'])->name('cashier.updateStatus');
    Route::get('/cashier/order/{orderId}', [CashierController::class, 'orderDetails'])->name('cashier.orderDetails');
    // New: reject order and rejection details
    Route::post('/cashier/order/{order}/reject', [CashierController::class, 'rejectOrder'])->name('cashier.reject');
});



// NOTIFICATION ROUTES
Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
Route::get('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');

// Customer resubmission of rejected order (re-upload payment proof)
Route::middleware(['auth'])->group(function() {
    Route::post('/orders/{order}/resubmit', function(\App\Models\Order $order, \Illuminate\Http\Request $request) {
        $user = \Illuminate\Support\Facades\Auth::user();
        if (!$user || $user->id !== $order->user_id) {
            return redirect()->route('products.index')->with('error', 'Unauthorized');
        }
        $validated = $request->validate([
            'payment_proof' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);
        // delete any previous proof if exists
        if ($order->payment_proof_path) {
            if (\Illuminate\Support\Facades\Storage::exists($order->payment_proof_path)) {
                \Illuminate\Support\Facades\Storage::delete($order->payment_proof_path);
            }
        }
        $path = $request->file('payment_proof')->store('payment_proofs', 'public');
        $order->payment_proof_path = $path;
        $order->processed = false; // back to pending
        $order->save();

        // notify managers/admins that order has been resubmitted
        $recipients = \App\Models\User::whereIn('role', ['admin','manager','cashier'])->get();
        foreach ($recipients as $recipient) {
            $recipient->notify(new \App\Notifications\OrderResubmitted($order));
        }

        return redirect()->route('orders.rejection', $order->id)->with('success', 'Payment proof re-uploaded. Your order is pending review.');
    })->name('orders.resubmit');
});
Route::patch('/orders/{order}', [CashierController::class, 'completeOrder']);
Route::get('/orders/{order}/rejection', [CashierController::class, 'rejectionView'])->name('orders.rejection');
Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');


Route::middleware(['web'])->group(function () {
    Route::post('/analytics/heartbeat', [\App\Http\Controllers\ProductController::class, 'heartbeat'])->name('analytics.heartbeat');
    Route::get('/analytics/online-count', [\App\Http\Controllers\ProductController::class, 'onlineCount'])->name('analytics.onlineCount');
    Route::post('/analytics/interaction', [InteractionController::class, 'record'])->name('analytics.interaction');
});