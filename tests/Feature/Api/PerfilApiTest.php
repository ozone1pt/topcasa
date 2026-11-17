<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PerfilApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_mostrar_perfil(): void
    {
        $user = User::factory()->create([
            'nome' => 'Ana',
            'apelido' => 'Silva',
            'telefone' => '912345678',
            'nif' => '100000002',
            'tipo_conta' => 'particular',
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/me')
            ->assertOk()
            ->assertJsonPath('data.nome', 'Ana')
            ->assertJsonPath('data.telefone', '912345678')
            ->assertJsonPath('data.nif', '100000002')
            ->assertJsonPath('data.tipo_conta', 'particular')
            ->assertJsonMissingPath('data.password');
    }

    public function test_atualizar_perfil_com_as_regras_do_site(): void
    {
        $user = User::factory()->create(['email' => 'ana@example.com']);
        Sanctum::actingAs($user);

        $this->putJson('/api/v1/me', [
            'nome' => 'Ana Maria',
            'apelido' => 'Silva',
            'email' => 'ana@example.com',
            'telefone' => '913333333',
            'nif' => '100000002',
            'tipo_conta' => 'profissional',
        ])->assertOk()
            ->assertJsonPath('data.nome', 'Ana Maria')
            ->assertJsonPath('data.nif', '100000002')
            ->assertJsonPath('data.tipo_conta', 'profissional');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'telefone' => '913333333',
            'tipo_conta' => 'profissional',
        ]);
    }

    public function test_mudar_email_limpa_a_verificacao(): void
    {
        $user = User::factory()->create(['email' => 'ana@example.com']);
        Sanctum::actingAs($user);

        $this->putJson('/api/v1/me', [
            'nome' => 'Ana',
            'email' => 'nova@example.com',
        ])->assertOk()->assertJsonPath('data.email_verified_at', null);

        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_nif_invalido_devolve_422(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->putJson('/api/v1/me', [
            'nome' => 'Ana',
            'email' => 'ana@example.com',
            'nif' => '100000003',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('nif')
            ->assertJsonStructure(['message', 'errors']);
    }

    public function test_empresa_exige_nome_da_empresa(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->putJson('/api/v1/me', [
            'nome' => 'Ana',
            'email' => $user->email,
            'tipo_conta' => 'empresa',
        ])->assertUnprocessable()->assertJsonValidationErrors('empresa_nome');
    }
}
