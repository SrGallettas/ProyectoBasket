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
        $subtitulo = $this->faker->text(150);
        $contenido = $this->faker->text(300);
        $slugBase = Str::slug($titulo);

        return [
            'titulo' => $titulo,
            'titulo_es' => fn (array $attributes) => $attributes['titulo'] ?? $titulo,
            'titulo_val' => null,
            'subtitulo' => $subtitulo,
            'subtitulo_es' => fn (array $attributes) => $attributes['subtitulo'] ?? $subtitulo,
            'subtitulo_val' => null,
            'contenido' => $contenido,
            'contenido_es' => fn (array $attributes) => $attributes['contenido'] ?? $contenido,
            'contenido_val' => null,
            'slug' => $slugBase . '-' . Str::lower(Str::random(6)),
            'imagen_noticia' => 'club_14.png',
            'autor' => $this->faker->name(),
            'oculto' => false,
            'created_at' => now(),
        ];
    }
}
