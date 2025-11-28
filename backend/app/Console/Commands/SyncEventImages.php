<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\Event;
use File;

class SyncEventImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'events:sync-images {--force : Force sync even if files exist}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync event images from public/uploads to storage/app/public';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting image sync process...');
        
        $uploadsPath = public_path('uploads/events');
        $storagePath = storage_path('app/public/uploads/events');
        
        // Ensure storage directory exists
        if (!File::exists($storagePath)) {
            File::makeDirectory($storagePath, 0755, true);
            $this->info('Created storage directory: ' . $storagePath);
        }
        
        // Get all events with image_url starting with 'uploads/'
        $events = Event::where('image_url', 'like', 'uploads/%')->get();
        
        $syncedCount = 0;
        $skippedCount = 0;
        
        foreach ($events as $event) {
            $sourceFile = public_path($event->image_url);
            $filename = basename($event->image_url);
            $destinationFile = $storagePath . '/' . $filename;
            
            if (File::exists($sourceFile)) {
                if (!File::exists($destinationFile) || $this->option('force')) {
                    try {
                        File::copy($sourceFile, $destinationFile);
                        $this->line("✓ Synced: {$filename}");
                        $syncedCount++;
                    } catch (\Exception $e) {
                        $this->error("✗ Failed to sync {$filename}: " . $e->getMessage());
                    }
                } else {
                    $this->line("- Skipped: {$filename} (already exists)");
                    $skippedCount++;
                }
            } else {
                $this->warn("? Source file not found: {$sourceFile}");
            }
        }
        
        $this->info("\nSync completed!");
        $this->info("Synced: {$syncedCount} files");
        $this->info("Skipped: {$skippedCount} files");
        
        // Check if storage link exists
        $linkPath = public_path('storage');
        if (!File::exists($linkPath)) {
            $this->warn("\nStorage link does not exist. Run: php artisan storage:link");
        } else {
            $this->info("\nStorage link exists: " . $linkPath);
        }
        
        return 0;
    }
}
