<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Merchandising>
 */
class MerchandisingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => $this->faker->randomElement([
                'Camiseta Oficial',
                'Sudadera Basket',
                'Pantalón Corto',
                'Bufanda Club',
                'Gorra',
                'Mochila Deportiva',
                'Calcetines Técnicos',
                'Balón Entrenamiento',
                'Camiseta Calentamiento',
                'Botella Térmica',
            ]),
            'descripcion' => $this->faker->sentence(10),
            'precio' => $this->faker->randomFloat(2, 10, 60), // Precio entre 10 y 60
            'imagen_merchan' => 'club_14.png',
            'talla' => $this->faker->randomElement(['S', 'M', 'L', 'XL', 'XXL', 'Talla Única', 'Sin Talla']),
        ];
    }
}
