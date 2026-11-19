<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminName = env('ADMIN_NAME', 'Store Administrator');
        $adminEmail = env('ADMIN_EMAIL', 'admin@gmail.com');
        $adminPassword = env('ADMIN_PASSWORD', 'admin123');

        $existingAdmin = User::where('email', $adminEmail)->first();

        if (! $existingAdmin) {
            User::create([
                'name' => $adminName,
                'email' => $adminEmail,
                'password' => Hash::make($adminPassword),
                'role' => 'admin',
            ]);
        } else {
            // Perbarui data admin dan pastikan memiliki role admin serta password terbaru
            $existingAdmin->update([
                'name' => $adminName,
                'password' => Hash::make($adminPassword),
                'role' => 'admin',
            ]);
        }
    }
}
