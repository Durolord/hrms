<?php

use Database\Seeders\ExtraPermissionsSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        (new ExtraPermissionsSeeder)->run();
    }

    public function down(): void {}
};
