<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Membuat akun Admin pertama supaya bisa langsung login
     * dan mengakses Admin Dashboard sejak awal development.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@supplychain.test'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );
    }
}