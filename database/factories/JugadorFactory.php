<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Jugador;


/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Jugador>
 */
class JugadorFactory extends Factory
{
    protected $model = Jugador::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => $this->faker->firstName(),
            'apellido' => $this->faker->lastName(),
            'posicion' => $this->faker->randomElement(['Base', 'Escolta', 'Alero', 'Ala-pívot', 'Pívot']),
            'dorsal' => $this->faker->numberBetween(1, 99),
            'fecha_nacimiento' => $this->faker->date('Y-m-d', $max = 'now'),
            'imagen_jugador' => 'club_14.png',
        ];
    }
}
