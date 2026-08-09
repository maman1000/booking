<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        User::create([
            'name' => 'Admin',
            'email' => 'admin@booking.test',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // User biasa (untuk testing)
        User::create([
            'name' => 'Customer',
            'email' => 'customer@booking.test',
            'password' => Hash::make('password'),
            'role' => 'customer',
        ]);
    }
}
