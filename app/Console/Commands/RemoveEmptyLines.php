<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class RemoveEmptyLines extends Command
{
    /**
     * The name and signature of the console command.
     *
     * You can pass an optional path argument.
     *
     * @var string
     */
    protected $signature = 'remove:empty-lines {path? : The directory to scan (default: project root)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove all empty lines from text-based files in a directory recursively';

    /**
     * Allowed file extensions to process.
     *
     * @var array
     */
    protected $allowedExtensions = ['php', 'blade.php'];

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Get the target path or default to the project base path.
        $path = $this->argument('path') ?? base_path();
        $files = File::allFiles($path);
        $processed = 0;
        foreach ($files as $file) {
            $filename = $file->getFilename();
            // Determine file extension.
            $extension = $file->getExtension();
            if ($this->isAllowed($filename, $extension)) {
                $content = $file->getContents();
                // Split content by newline.
                $lines = explode("\n", $content);
                // Filter out lines that are empty (after trimming whitespace).
                $nonEmptyLines = array_filter($lines, function ($line) {
                    return trim($line) !== '';
                });
                // Join the non-empty lines back together.
                $newContent = implode("\n", $nonEmptyLines);
                // Write back the file only if content has changed.
                if ($newContent !== $content) {
                    File::put($file->getRealPath(), $newContent);
                    $this->info('Processed: '.$file->getRealPath());
                    $processed++;
                }
            }
        }
        $this->info("Finished processing {$processed} files.");

        return 0;
    }

    /**
     * Determine if the file should be processed.
     *
     * @param  string  $filename
     * @param  string  $extension
     * @return bool
     */
    protected function isAllowed($filename, $extension)
    {
        // Ensure Blade files (ending with "blade.php") are processed.
        if (substr($filename, -10) === 'blade.php') {
            return true;
        }

        return in_array($extension, $this->allowedExtensions);
    }
}
