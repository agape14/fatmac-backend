<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear usuario Admin
        User::create([
            'name' => 'Administrador',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'phone_number' => '+1234567890',
            'status' => 'approved',
        ]);

        // Crear usuario Vendor (aprobado para testing)
        User::create([
            'name' => 'Vendedor',
            'email' => 'vendor@example.com',
            'password' => Hash::make('password'),
            'role' => 'vendor',
            'phone_number' => '+1234567891',
            'status' => 'approved',
        ]);

        // Crear usuario Cliente (para testing)
        User::create([
            'name' => 'Cliente',
            'email' => 'cliente@example.com',
            'password' => Hash::make('password'),
            'role' => 'customer',
            'phone_number' => '+1234567892',
            'status' => 'approved',
        ]);
    }
}
