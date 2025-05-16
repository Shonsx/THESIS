<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GCash;
use Illuminate\Support\Facades\Storage;

class GCashController extends Controller
{
    public function index()
    {
        $gcash = GCash::latest()->first();
        return view('etry.gcash', compact('gcash'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'gcash_image' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $file = $request->file('gcash_image');
        $path = $file->store('gcash', 'public');

        // Check and delete the old image
        $gcash = GCash::latest()->first();
        if ($gcash && $gcash->image_path && Storage::disk('public')->exists($gcash->image_path)) {
            Storage::disk('public')->delete($gcash->image_path);
        }

        // Update or create new
        if ($gcash) {
            $gcash->update(['image_path' => $path]);
        } else {
            GCash::create(['image_path' => $path]);
        }

        return redirect()->route('gcash.index')->with('success', 'GCash image uploaded successfully!');
    }
}
