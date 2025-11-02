<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if admin already exists to prevent duplicates
        if (!User::where('name', 'admin')->exists()) {
            User::create([
                'name' => 'admin',
                'email' => 'admin@temp.com', // Temporary email, will be updated on first login
                'password' => Hash::make('admiN123456789'),
                'tel' => null, // Will be set on first login
                'role' => 'admin',
                'first_login' => true, // Flag to track first login
            ]);
        }
    }
}