<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\SiteVisit;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        // Log visit for analytics
        try {
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
        } catch (\Throwable $e) {}

        // Fetch latest 8 products
        $products = Product::latest()->take(8)->get();

        return view('welcome', compact('products'));
    }
}
