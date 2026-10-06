<?php

namespace App\Console\Commands;

use App\Support\Demo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class DemoReset extends Command
{
    protected $signature = 'demo:reset {--force : Reset even when DEMO_MODE is off (wipes the database)}';

    protected $description = 'Wipe and re-seed the public demo database (refuses unless DEMO_MODE=true or --force)';

    public function handle(): int
    {
        if (! Demo::enabled() && ! $this->option('force')) {
            $this->error('Refusing to reset: DEMO_MODE is not enabled. Pass --force to wipe and re-seed anyway.');

            return self::FAILURE;
        }

        foreach (['cvs', 'avatars', 'applicants'] as $directory) {
            Storage::disk('public')->deleteDirectory($directory);
            Storage::disk('local')->deleteDirectory($directory);
        }
        $status = $this->call('migrate:fresh', ['--seed' => true, '--force' => true]);
        if ($status !== self::SUCCESS) {
            $this->error('Demo reset failed while re-seeding.');

            return self::FAILURE;
        }
        $this->info('Demo data reset. Next reset: '.Demo::nextResetAt()->toDayDateTimeString().'.');

        return self::SUCCESS;
    }
}
