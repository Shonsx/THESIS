<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;

class HomeController extends Controller
{
    public function index()
    {
        // Fetch latest 10 products
        $products = Product::latest()->take(8)->get();

        // Pass to the view (home.blade.php or equivalent)
        return view('welcome', compact('products'));
    }
}
