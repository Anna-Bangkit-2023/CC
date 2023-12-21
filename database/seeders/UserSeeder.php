<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = [
            [
                'id' => 1, // 'id' => '1
                'name' => 'Admin',
                'phone' => '081234567890',
                'email' => 'charderrabagas@gmail.com',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'type' => 'user',
                'membership' => 'free',
            ],
            [
                'id' => 2, // 'id' => '2
                'name' => 'ANNA - BOT',
                'phone' => '081234567890',
                'email' => 'c204bsy3770@bangkit.academy',
                'password' => Hash::make('anna123'),
                'role' => 'user',
                'type' => 'bot',
                'membership' => 'free',
            ],
        ];

        User::insert($user);
    }
}
