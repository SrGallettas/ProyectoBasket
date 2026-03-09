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
        // Usuario fijo para acceso conocido al panel.
        User::updateOrCreate(
            ['email' => 'admin@bellreguard.com'],
            [
                'name' => 'Admin Bellreguard',
                'password' => Hash::make('12345678'),
            ]
        );

        // Usuarios de apoyo para poblar la tabla de administración.
        User::factory()->count(5)->create();
    }
}
