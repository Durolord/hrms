<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class RemoveSingleLineComments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * You can optionally pass a path (defaults to the project root).
     *
     * @var string
     */
    protected $signature = 'remove:single-line-comments {path? : The directory to scan (default: project root)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove all single-line PHP comments from all PHP files in a directory';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $path = $this->argument('path') ?? base_path();
        $files = File::allFiles($path);
        $processed = 0;
        foreach ($files as $file) {
            if ($file->getExtension() === 'php') {
                $content = $file->getContents();
                $cleanContent = $this->removeSingleLineComments($content);
                if ($cleanContent !== $content) {
                    File::put($file->getRealPath(), $cleanContent);
                    $this->info('Processed: '.$file->getRealPath());
                    $processed++;
                }
            }
        }
        $this->info("Finished processing {$processed} PHP files.");

        return 0;
    }

    /**
     * Remove single-line comments from PHP code.
     *
     * @param  string  $code  The original PHP code.
     * @return string The cleaned code.
     */
    protected function removeSingleLineComments($code)
    {
        $tokens = token_get_all($code);
        $cleanCode = '';
        foreach ($tokens as $token) {
            if (is_array($token)) {
                if ($token[0] === T_COMMENT) {
                    if (strpos($token[1], "\n") === false) {
                        continue;
                    }
                }
                $cleanCode .= $token[1];
            } else {
                $cleanCode .= $token;
            }
        }

        return $cleanCode;
    }
}
