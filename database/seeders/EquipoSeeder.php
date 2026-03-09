<?php

namespace Database\Seeders;

use App\Models\Equipo;
use Illuminate\Database\Seeder;

class EquipoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categorias = ['Infantil', 'Cadete', 'Junior', 'Senior', 'Alevín'];

        foreach ($categorias as $categoria) {
            for ($i = 1; $i <= 2; $i++) {
                Equipo::create([
                    'nombre' => sprintf('%s Bellreguard %s', $categoria, $i),
                    'categoria' => $categoria,
                    'imagen_club' => 'club_14.png',
                    'es_local' => true,
                ]);
            }
        }
    }
}
