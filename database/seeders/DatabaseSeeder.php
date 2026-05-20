<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
    
        User::create([
            'name' => 'admin',
            'email' => 'hallo@jonusa.net',
            'password' => Hash::make('password123'),
            'role' => 'kepala',
            'is_default_password' => false,
        ]);
    }
}