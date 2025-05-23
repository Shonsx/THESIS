<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
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


Route::get('/product', [ProductController::class, 'index'])->name('products.index');

Route::get('/login', function () {
    return view('etry.login');
});

Route::get('/signup', function () {
    return view('etry.signup');
});

Route::get('/try-on/{id}/test', function ($id) {
    $productFolderPath = public_path("ar/product{$id}");

    if (!is_dir($productFolderPath)) {
        abort(404, 'Product folder not found.');
    }

    // Find subfolders inside the product folder
    $subfolders = array_filter(glob($productFolderPath . '/*'), 'is_dir');

    $testFilePath = null;

    foreach ($subfolders as $subfolder) {
        $possibleTestFile = $subfolder . '/test.html'; // Directly inside subfolder, NOT in build/
        if (file_exists($possibleTestFile)) {
            $testFilePath = $possibleTestFile;
            break;
        }
    }

    if (!$testFilePath) {
        abort(404, 'Try-on file not found.');
    }

    // Return the file so the browser loads the test.html directly
    return response()->file($testFilePath);
})->name('tryon.test');






Route::post('/register', [AuthController::class,'register'])->name('register');
Route::post('/login', [AuthController::class,'login'])->name('login');
Route::post('logout', [AuthController::class,'logout'])->name('logout');

Route::get('/addProduct', [ProductController::class, 'create'])->name('addProduct');
Route::post('/addProduct', [ProductController::class, 'store'])->name('addProduct.store');
Route::get('/products/{id}', [ProductController::class, 'show'])->name('products.show');
Route::delete('/products/delete/{id}', [ProductController::class, 'destroy'])->middleware('auth');
Route::put('/products/{id}', [ProductController::class, 'update'])->name('products.update');
Route::get('/etry/gcash', function () {
    return view('etry.gcash');
})->name('gcash.page');
Route::get('/gcash', [GCashController::class, 'index'])->name('gcash.index');
Route::post('/gcash', [GCashController::class, 'store'])->name('gcash.store');




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
Route::get('/cashier', function() {
    return view('cashier.main');
})->middleware('auth')->name('cashier.main');
Route::get('/cashier/order/{id}', [CashierController::class, 'orderDetails'])->name('cashier.orderDetails');


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

//CASHIER CONTROL
Route::get('/cashier', function () {
    if (Auth::user()->role !== 'cashier') {
        return redirect()->route('welcome')->with('error', 'Unauthorized');
    }

    $orders = Order::where('processed', false)->get();
    return view('cashier.main', compact('orders'));
})->name('cashier.main')->middleware('auth');
Route::patch('/cashier/complete/{id}', function ($id) {
    $order = Order::findOrFail($id);
    $order->update(['processed' => true]);

    return redirect()->route('cashier.main')->with('success', 'Order completed!');
})->name('cashier.complete')->middleware('auth');
Route::post('/checkout/process', [CheckoutController::class, 'process'])->name('checkout.process');
Route::patch('/cashier/order/{order}', [CashierController::class, 'updateStatus'])->name('cashier.updateStatus');
Route::get('/cashier/history', [CashierController::class, 'history'])->name('cashier.history');
Route::patch('/cashier/update-status/{order}', [CashierController::class, 'updateStatus'])->name('cashier.updateStatus');
Route::get('/cashier/order/{orderId}', [CashierController::class, 'orderDetails'])->name('cashier.orderDetails');



// NOTIFICATION ROUTES
Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
Route::get('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
Route::patch('/orders/{order}', [CashierController::class, 'completeOrder']);
Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');



Route::get('/logout', function () {
    Auth::logout();
    return redirect('welcome');
})->name('logout');