<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GaleriaImageControllerTest extends TestCase
{
    public function test_devuelve_una_imagen_de_galeria_desde_storage_publico(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('galerias/temporada-2026/foto-1.jpg', 'contenido-imagen');

        $response = $this->get('/media/galerias/temporada-2026/foto-1.jpg');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/jpeg');
        $response->assertSee('contenido-imagen');
    }
}
