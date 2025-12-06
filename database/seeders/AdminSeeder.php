<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $Admin = User::updateOrCreate(
            ['email' => 'ronaldjjuuko7@gmail.com'],
            [
                'name' => 'FPL Galaxy Admin',
                'password' => Hash::make('88928892'),
                'email_verified_at' => now(),
                'status' => 'active',
                'role' => 'admin',
                'signup_method' => 'seed',

            ]
        );
    }
}
