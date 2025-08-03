<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Ahmad Rizki',
                'email' => 'ahmad.rizki@pekanbaru.go.id',
                'password' => Hash::make('password123'),
                'role' => 'super_admin',
                'department' => 'Diskominfotik',
                'phone' => '0761-123-4567',
                'status' => 'active',
            ],
            [
                'name' => 'Siti Nurhaliza',
                'email' => 'siti.nurhaliza@pekanbaru.go.id',
                'password' => Hash::make('password123'),
                'role' => 'editor',
                'department' => 'Humas',
                'phone' => '0761-234-5678',
                'status' => 'active',
            ],
            [
                'name' => 'Budi Santoso',
                'email' => 'budi.santoso@pekanbaru.go.id',
                'password' => Hash::make('password123'),
                'role' => 'editor',
                'department' => 'IT Support',
                'phone' => '0761-345-6789',
                'status' => 'active',
            ],
            [
                'name' => 'Maya Sari',
                'email' => 'maya.sari@pekanbaru.go.id',
                'password' => Hash::make('password123'),
                'role' => 'editor',
                'department' => 'Publikasi',
                'phone' => '0761-456-7890',
                'status' => 'inactive',
            ],
            [
                'name' => 'Dedi Kurniawan',
                'email' => 'dedi.kurniawan@pekanbaru.go.id',
                'password' => Hash::make('password123'),
                'role' => 'super_admin',
                'department' => 'Diskominfotik',
                'phone' => '0761-567-8901',
                'status' => 'active',
            ],
        ];

        foreach ($users as $userData) {
            User::create($userData);
        }
    }
}