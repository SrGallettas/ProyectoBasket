<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Equipo;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Equipo>
 */
class EquipoFactory extends Factory
{
    protected $model = Equipo::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "nombre" => $this->faker->city() . ' Basket Club',
            "categoria" => $this->faker->randomElement(['Senior', 'Junior', 'Cadete']),
            'numero_jugadores' => 12,
        ];
    }
}
