<?php
/**
 * Standalone Image Migration Script for Cloudways
 * Run this with: php migrate-images.php
 */

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\GCash;

echo "=== Image Migration Script ===\n";
echo "Migrating images from public/images/* to storage/app/public/*\n\n";

// Ensure destination directories exist
echo "Creating destination directories...\n";
foreach (['products', 'measurements', 'gcash'] as $dir) {
    if (!Storage::disk('public')->exists($dir)) {
        Storage::disk('public')->makeDirectory($dir);
        echo " ✓ Created: {$dir}\n";
    } else {
        echo " ✓ Exists: {$dir}\n";
    }
}

$copied = 0;
$skipped = 0;

// Copy product images
echo "\nCopying product images...\n";
$productDir = public_path('images/products');
if (File::exists($productDir)) {
    $files = File::files($productDir);
    foreach ($files as $file) {
        $filename = $file->getFilename();
        $sourcePath = $file->getPathname();
        $destPath = "products/{$filename}";
        
        if (!Storage::disk('public')->exists($destPath)) {
            Storage::disk('public')->put($destPath, File::get($sourcePath));
            echo " ✓ Copied: {$filename}\n";
            $copied++;
        } else {
            echo " - Skipped (exists): {$filename}\n";
            $skipped++;
        }
    }
}

// Copy measurement images
echo "\nCopying measurement images...\n";
$measurementDir = public_path('images/measurements');
if (File::exists($measurementDir)) {
    $files = File::files($measurementDir);
    foreach ($files as $file) {
        $filename = $file->getFilename();
        $sourcePath = $file->getPathname();
        $destPath = "measurements/{$filename}";
        
        if (!Storage::disk('public')->exists($destPath)) {
            Storage::disk('public')->put($destPath, File::get($sourcePath));
            echo " ✓ Copied: {$filename}\n";
            $copied++;
        } else {
            echo " - Skipped (exists): {$filename}\n";
            $skipped++;
        }
    }
}

// Copy GCash images
echo "\nCopying GCash images...\n";
$gcashDir = public_path('images/gcash');
if (File::exists($gcashDir)) {
    $files = File::files($gcashDir);
    foreach ($files as $file) {
        $filename = $file->getFilename();
        $sourcePath = $file->getPathname();
        $destPath = "gcash/{$filename}";
        
        if (!Storage::disk('public')->exists($destPath)) {
            Storage::disk('public')->put($destPath, File::get($sourcePath));
            echo " ✓ Copied: {$filename}\n";
            $copied++;
        } else {
            echo " - Skipped (exists): {$filename}\n";
            $skipped++;
        }
    }
}

// Normalize database paths
echo "\nNormalizing database paths...\n";
$updated = 0;

// Update product images
$products = Product::whereNotNull('image')->get();
foreach ($products as $product) {
    $oldPath = $product->image;
    if (str_starts_with($oldPath, 'images/products/')) {
        $newPath = str_replace('images/products/', 'products/', $oldPath);
        $product->update(['image' => $newPath]);
        echo " ✓ Updated product: {$oldPath} → {$newPath}\n";
        $updated++;
    }
}

// Update GCash images
$gcashes = GCash::whereNotNull('image')->get();
foreach ($gcashes as $gcash) {
    $oldPath = $gcash->image;
    if (str_starts_with($oldPath, 'images/gcash/')) {
        $newPath = str_replace('images/gcash/', 'gcash/', $oldPath);
        $gcash->update(['image' => $newPath]);
        echo " ✓ Updated GCash: {$oldPath} → {$newPath}\n";
        $updated++;
    }
}

echo "\n=== Migration Complete ===\n";
echo "Files copied: {$copied}\n";
echo "Files skipped: {$skipped}\n";
echo "Database paths updated: {$updated}\n";
echo "\nYou can now run: php artisan storage:link\n";