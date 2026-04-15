<?php

namespace Database\Seeders;

use App\Models\Equipo;
use App\Models\Jugador;
use Illuminate\Database\Seeder;

class JugadorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Equipo::query()
            ->where('es_local', true)
            ->each(function (Equipo $equipo): void {
            Jugador::factory()->count(6)->create([
                'equipo_id' => $equipo->id,
            ]);
        });
    }
}
