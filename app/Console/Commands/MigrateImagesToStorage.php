<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Product;
use App\Models\GCash;

class MigrateImagesToStorage extends Command
{
    protected $signature = 'assets:migrate-storage {--dry-run : Show what would change without writing}';
    protected $description = 'Copy images from public/images/* to storage/app/public/* and normalize DB paths.';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');

        $this->info('Ensuring destination directories exist on public disk...');
        foreach (['products','measurements','gcash'] as $dir) {
            if (!Storage::disk('public')->exists($dir)) {
                if (!$dry) Storage::disk('public')->makeDirectory($dir);
                $this->line(" - created: {$dir}");
            } else {
                $this->line(" - exists: {$dir}");
            }
        }

        // Map of source (under public/images) to destination (storage/app/public)
        $map = [
            'products' => public_path('images/products'),
            'measurements' => public_path('images/measurements'),
            'gcash' => public_path('images/gcash'),
        ];

        $this->info('Copying files from public/images/* to storage/app/public/* ...');
        $copied = 0; $skipped = 0;
        foreach ($map as $bucket => $sourceDir) {
            if (!is_dir($sourceDir)) {
                $this->warn(" - source not found: {$sourceDir} (skipping)");
                continue;
            }
            $files = File::files($sourceDir);
            foreach ($files as $file) {
                $filename = $file->getFilename();
                $destPath = $bucket . '/' . $filename;
                if (Storage::disk('public')->exists($destPath)) {
                    $skipped++;
                    continue;
                }
                if (!$dry) {
                    Storage::disk('public')->put($destPath, File::get($file->getPathname()));
                }
                $copied++;
            }
            $this->line(" - {$bucket}: copied {$copied}, skipped {$skipped}");
        }

        $this->info('Normalizing DB paths for Product images...');
        $updatedProducts = 0;
        Product::whereNotNull('image')
            ->orderBy('id')
            ->chunkById(200, function ($chunk) use (&$updatedProducts, $dry) {
                foreach ($chunk as $p) {
                    $original = (string) $p->image;
                    $filename = basename($original);
                    $normalized = 'products/' . ltrim($filename, '/');
                    if ($original !== $normalized) {
                        if (!$dry) { $p->image = $normalized; $p->save(); }
                        $updatedProducts++;
                    }
                }
            });

        $this->info('Normalizing DB paths for Product measurement images...');
        $updatedMeasurements = 0;
        Product::whereNotNull('measurement_image')
            ->orderBy('id')
            ->chunkById(200, function ($chunk) use (&$updatedMeasurements, $dry) {
                foreach ($chunk as $p) {
                    $original = (string) $p->measurement_image;
                    $filename = basename($original);
                    $normalized = 'measurements/' . ltrim($filename, '/');
                    if ($original !== $normalized) {
                        if (!$dry) { $p->measurement_image = $normalized; $p->save(); }
                        $updatedMeasurements++;
                    }
                }
            });

        $this->info('Normalizing DB paths for GCash image...');
        $updatedGcash = 0;
        GCash::whereNotNull('image_path')->orderBy('id')->chunkById(200, function ($chunk) use (&$updatedGcash, $dry) {
            foreach ($chunk as $g) {
                $original = (string) $g->image_path;
                $filename = basename($original);
                $normalized = 'gcash/' . ltrim($filename, '/');
                if ($original !== $normalized) {
                    if (!$dry) { $g->image_path = $normalized; $g->save(); }
                    $updatedGcash++;
                }
            }
        });

        $this->newLine();
        $this->info("Summary:");
        $this->line(" - Files copied: {$copied}");
        $this->line(" - Files skipped (already exist): {$skipped}");
        $this->line(" - Product image paths updated: {$updatedProducts}");
        $this->line(" - Measurement image paths updated: {$updatedMeasurements}");
        $this->line(" - GCash image paths updated: {$updatedGcash}");

        $this->newLine();
        $this->info($dry ? 'Dry-run complete. No changes were written.' : 'Migration complete.');
        return self::SUCCESS;
    }
}