<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str; // Para el SLUG

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Noticia>
 */
class NoticiaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $titulo = $this->faker->sentence(6);
        $slugBase = Str::slug($titulo);

        return [
            'titulo' => $titulo,
            'subtitulo' => $this->faker->text(150),
            'contenido' => $this->faker->text(300),
            'slug' => $slugBase . '-' . Str::lower(Str::random(6)),
            'imagen_noticia' => 'club_14.png',
            'autor' => $this->faker->name(),
            'oculto' => false,
            'created_at' => now(),
        ];
    }
}
