<?php

namespace Database\Seeders;

use App\Models\Equipo;
use App\Models\Partido;
use Illuminate\Database\Seeder;

class PartidoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categorias = ['Infantil', 'Cadete', 'Junior', 'Senior', 'Alevín'];

        foreach ($categorias as $categoria) {
            $equipos = Equipo::query()
                ->where('categoria', $categoria)
                ->orderBy('id')
                ->take(2)
                ->get();

            if ($equipos->count() < 2) {
                continue;
            }

            Partido::factory()->create([
                'equipo_local_id' => $equipos[0]->id,
                'equipo_visitante_id' => $equipos[1]->id,
                'categoria' => $categoria,
            ]);
        }
    }
}
