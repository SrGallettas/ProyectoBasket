<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Equipo;
use App\Models\Jugador;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. CREAR EL USUARIO ADMINISTRADOR
        User::create([
            'name' => 'Admin Bellreguard',
            'email' => 'admin@bellreguard.com',
            'password' => '12345678',
        ]);

        // 2. CREAR 3 EQUIPOS
        $equipos = Equipo::factory()->count(3)->create();

        // 3. RELACIONAR JUGADORES (Relación Uno a Muchos)
        $equipos->each(function ($equipo) {

            // Por cada equipo ($equipo), creamos 12 jugadores
            Jugador::factory()->count(12)->create([
                'equipo_id' => $equipo->id // Los vinculamos mediante la foreign key
            ]);
        });
    }
}
