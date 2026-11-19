<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Testing User',
                'email' => 'test@gmail.com',
                'password' => Hash::make('123'),
            ],
            [
                'name' => 'Talenavi Tester',
                'email' => 'test@talenavi.test',
                'password' => Hash::make('123'),
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => $userData['password'],
                ]
            );
        }
    }
}
