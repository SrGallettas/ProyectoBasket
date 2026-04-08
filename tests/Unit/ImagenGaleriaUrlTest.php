<?php

namespace Tests\Unit;

use App\Models\ImagenGaleria;
use Tests\TestCase;

class ImagenGaleriaUrlTest extends TestCase
{
    public function test_genera_la_url_publica_desde_storage(): void
    {
        $imagen = new ImagenGaleria([
            'archivo' => 'temporada-2026/foto-1.jpg',
        ]);

        $this->assertSame('/storage/galerias/temporada-2026/foto-1.jpg', $imagen->image_url);
    }
}
