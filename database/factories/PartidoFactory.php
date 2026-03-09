<?php

namespace Database\Factories;

use App\Models\Partido;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Partido>
 */
class PartidoFactory extends Factory
{
    protected $model = Partido::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categorias = ['Infantil', 'Cadete', 'Junior', 'Senior', 'Alevín'];

        return [
            'equipo_local_id' => 1,
            'equipo_visitante_id' => 2,
            'fecha_hora' => $this->faker->dateTimeBetween('+1 day', '+2 months'),
            'lugar' => $this->faker->randomElement([
                'Pabellón Municipal de Bellreguard',
                'Polideportivo Local',
                'Pista Central',
                'Pabellón Comarcal',
            ]),
            'categoria' => $this->faker->randomElement($categorias),
        ];
    }
}
