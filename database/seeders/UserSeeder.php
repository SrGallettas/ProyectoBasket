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
        // Usuario admin fijo para acceso completo al panel.
        User::updateOrCreate(
            ['email' => 'admin@bellreguard.com'],
            [
                'name' => 'Admin Bellreguard',
                'role' => User::ROLE_ADMIN,
                'password' => Hash::make('12345678'),
            ]
        );

        // Usuario entrenador fijo para probar los permisos limitados.
        User::updateOrCreate(
            ['email' => 'coach@bellreguard.com'],
            [
                'name' => 'Coach Bellreguard',
                'role' => User::ROLE_COACH,
                'password' => Hash::make('12345678'),
            ]
        );

        User::updateOrCreate(
            ['email' => 'editor@bellreguard.com'],
            [
                'name' => 'Editor Bellreguard',
                'role' => User::ROLE_EDITOR,
                'password' => Hash::make('12345678'),
            ]
        );
    }
}
