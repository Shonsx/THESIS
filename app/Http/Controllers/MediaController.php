<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class MediaController extends Controller
{
    public function show(string $path)
    {
        if (!Storage::disk('public')->exists($path)) {
            abort(404);
        }

        $fullPath = Storage::disk('public')->path($path);
        $mime = File::mimeType($fullPath) ?? 'application/octet-stream';
        $content = File::get($fullPath);

        return response($content, 200)->header('Content-Type', $mime);
    }
}