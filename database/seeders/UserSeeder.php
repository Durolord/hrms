<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        $roles = ['Admin', 'HR Manager', 'Finance Manager', 'Department Head', 'Employee', 'IT Admin',
        ];
        $nigerianNames = [
            'Admin' => ['Olumide Adebayo', 'Chimamanda Obi', 'Tunde Alabi', 'Ngozi Oke', 'Femi Adekunle'],
            'HR Manager' => ['Adeola Afolabi', 'Kemi Balogun', 'Chike Nwosu', 'Funmi Okeke', 'Ifeoma Eze'],
            'Finance Manager' => ['Bolanle Adebayo', 'Chinonso Umeh', 'Damilola Ibitoye', 'Oluwakemi Lawal', 'Chinedu Okafor'],
            'Department Head' => ['Abdul Rahman', 'Eze Iwuchukwu', 'Sadiq Salami', 'Titi Bakare', 'Gbenga Ogunleye'],
            'Payroll Officer' => ['Kehinde Afolabi', 'Durojaiye Olatunde', 'Bola Akinlolu', 'Omotayo Olumide', 'Adebayo Folake'],
            'Employee' => ['Ugochukwu Okonkwo', 'Nnena Nwachukwu', 'Chijioke Eze', 'Chiamaka Oduh', 'Folasade Alabi'],
            'IT Admin' => ['Olumide Adeyemi', 'Yemi Oluwaseun', 'Olugbenga Idowu', 'Titi Oshodi', 'Seyi Ayodele'],
            'Auditor' => ['Ademola Adegbola', 'Funke Abiola', 'Segun Olorunfemi', 'Tolu Ogunleye', 'Tope Falana'],
        ];
        foreach ($roles as $role) {
            foreach ($nigerianNames[$role] as $i => $name) {
                $user = User::create([
                    'name' => $name,
                    'email' => strtolower(str_replace(' ', '_', $name)).'@example.com',
                    'password' => Hash::make('password'),
                ]);
                $user->assignRole($role);
            }
        }
    }
}
