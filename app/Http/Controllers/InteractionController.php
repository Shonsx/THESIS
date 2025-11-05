<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ContentInteraction;
use App\Models\Product;

class InteractionController extends Controller
{
    public function record(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer|exists:products,id',
        ]);

        try {
            ContentInteraction::create([
                'product_id' => $request->input('product_id'),
            ]);
        } catch (\Throwable $e) {
            // Swallow errors to avoid impacting UX
        }

        return response()->noContent();
    }
}