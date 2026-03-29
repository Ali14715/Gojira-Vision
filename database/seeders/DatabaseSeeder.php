<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@gojiravision.com',
            'password' => 'password',
            'role' => 'admin',
            'position' => 'System Admin',
        ]);

        User::create([
            'name' => 'Budi Karyawan',
            'email' => 'budi@gojiravision.com',
            'password' => 'password',
            'role' => 'karyawan',
            'position' => 'Staff IT',
            'phone' => '081234567890',
        ]);
    }
}
