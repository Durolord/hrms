<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\Demo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * The shared role accounts from config/demo.php. Idempotent: re-running restores each account's name,
 * password and single role. Runs after ShieldSeeder/ExtraPermissionsSeeder so the roles exist;
 * EmployeeSeeder later gives each account an employee record.
 */
class DemoAccountSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Demo::accounts() as $account) {
            $user = User::updateOrCreate(
                ['email' => $account['email']],
                ['name' => $account['name'], 'password' => Hash::make(Demo::password())],
            );
            $user->syncRoles([$account['role']]);
        }
    }
}
