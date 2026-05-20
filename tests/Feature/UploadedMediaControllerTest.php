<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UploadedMediaControllerTest extends TestCase
{
    public function test_devuelve_una_imagen_de_merchandising_desde_storage_publico(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('merchandising/camiseta.png', 'contenido-merch');

        $response = $this->get('/media/uploads/merchandising/camiseta.png');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/png');
        $response->assertSee('contenido-merch');
    }

    public function test_devuelve_un_escudo_desde_storage_publico(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('escudos/club.png', 'contenido-escudo');

        $response = $this->get('/media/uploads/escudos/club.png');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/png');
        $response->assertSee('contenido-escudo');
    }
}
