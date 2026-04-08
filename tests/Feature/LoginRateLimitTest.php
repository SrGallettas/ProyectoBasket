<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginRateLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_bloquea_el_login_tras_cinco_intentos_fallidos(): void
    {
        User::factory()->create([
            'email' => 'admin@bellreguard.com',
            'password' => 'password123',
        ]);

        for ($intento = 0; $intento < 5; $intento++) {
            $this->post(route('login'), [
                'email' => 'admin@bellreguard.com',
                'password' => 'incorrecta',
            ])->assertSessionHasErrors('email');
        }

        $this->post(route('login'), [
            'email' => 'admin@bellreguard.com',
            'password' => 'incorrecta',
        ])->assertSessionHasErrors([
            'email' => 'Demasiados intentos de acceso. Vuelve a intentarlo en 15 minuto(s).',
        ]);
    }
}
