<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * Urutan run() penting: AdminUserSeeder duluan supaya akun admin
     * langsung tersedia, lalu kamus sentiment untuk Tahap 7.
     */
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            PositiveWordSeeder::class,
            NegativeWordSeeder::class,
        ]);
    }
}