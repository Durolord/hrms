<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class DemoReset extends Command
{
    protected $signature = 'demo:reset';

    protected $description = 'Wipe and re-seed the public demo database (only runs when DEMO_MODE=true)';

    public function handle(): int
    {
        if (! config('app.demo')) {
            $this->error('Refusing to reset: DEMO_MODE is not enabled.');

            return self::FAILURE;
        }

        Storage::disk('public')->deleteDirectory('applicants');
        Artisan::call('migrate:fresh', ['--seed' => true, '--force' => true]);
        $this->info('Demo data reset.');

        return self::SUCCESS;
    }
}
