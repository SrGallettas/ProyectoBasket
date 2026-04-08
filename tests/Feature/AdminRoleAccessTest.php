<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_coach_no_puede_entrar_a_gestion_de_usuarios(): void
    {
        $coach = User::factory()->create([
            'role' => User::ROLE_COACH,
        ]);

        $response = $this->actingAs($coach)->get(route('usuarios.index'));

        $response->assertForbidden();
    }

    public function test_un_admin_si_puede_entrar_a_gestion_de_usuarios(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $response = $this->actingAs($admin)->get(route('usuarios.index'));

        $response->assertOk();
    }

    public function test_un_editor_puede_entrar_a_noticias_pero_no_a_usuarios(): void
    {
        $editor = User::factory()->create([
            'role' => User::ROLE_EDITOR,
        ]);

        $this->actingAs($editor)
            ->get(route('noticias.index'))
            ->assertOk();

        $this->actingAs($editor)
            ->get(route('usuarios.index'))
            ->assertForbidden();
    }

    public function test_un_coach_si_puede_entrar_a_gestion_de_equipos(): void
    {
        $coach = User::factory()->create([
            'role' => User::ROLE_COACH,
        ]);

        $response = $this->actingAs($coach)->get(route('equipos.index'));

        $response->assertOk();
    }
}
