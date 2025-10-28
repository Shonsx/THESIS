<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use App\Models\Review;

class ReviewController extends Controller
{
    public function create(Product $product)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        return view('etry.review-create', compact('product'));
    }

    public function store(Request $request, Product $product)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $request->validate([
            'rating' => ['required', 'numeric', 'between:0.5,5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        // Enforce half-star increments
        $rating = (float) $request->input('rating');
        if (fmod($rating, 0.5) !== 0.0) {
            return back()->withErrors(['rating' => 'Rating must be in 0.5 increments.'])->withInput();
        }

        Review::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'rating' => $rating,
            'comment' => $request->input('comment'),
        ]);

        return redirect()->route('products.show', $product->id)->with('success', 'Thank you for your review!');
    }
}