<?php

namespace Tests\Feature\Api;

use App\Models\Anuncio;
use App\Models\Categoria;
use App\Models\Distrito;
use App\Models\GaleriaAnuncio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AnuncioGestaoApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_criar_anuncio_autenticado_fica_pendente(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/anuncios', $this->dados())
            ->assertCreated()
            ->assertJsonPath('data.status', 'pendente')
            ->assertJsonPath('data.titulo', 'Canalizador')
            ->assertJsonPath('data.contacto', '912345678');

        $this->assertDatabaseHas('anuncios', [
            'user_id' => $user->id,
            'titulo' => 'Canalizador',
            'status' => 'pendente',
        ]);
    }

    public function test_visitante_nao_cria_anuncio(): void
    {
        $this->postJson('/api/v1/anuncios', $this->dados())->assertUnauthorized();
    }

    public function test_criar_com_imagem_e_gerir_galeria(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $criar = $this->post('/api/v1/anuncios', [
            ...$this->dados(),
            'imagens' => [
                UploadedFile::fake()->image('a.jpg'),
                UploadedFile::fake()->image('b.png'),
            ],
        ], ['Accept' => 'application/json']);

        $criar->assertCreated();
        $id = $criar->json('data.id');
        $imagens = collect($criar->json('data.imagens'));
        $this->assertCount(2, $imagens);

        $this->postJson('/api/v1/galeria/ordenar', [
            'ids' => [$imagens[1]['id'], $imagens[0]['id']],
        ])->assertOk();

        $this->assertSame(1, GaleriaAnuncio::find($imagens[1]['id'])->ordem);

        $this->postJson('/api/v1/galeria/'.$imagens[0]['id'].'/rodar', ['sentido' => 'direita'])
            ->assertOk()
            ->assertJsonStructure(['url']);

        $this->deleteJson('/api/v1/galeria/'.$imagens[0]['id'])->assertOk();
        $this->assertDatabaseMissing('galeria_anuncios', ['id' => $imagens[0]['id']]);

        $this->put('/api/v1/anuncios/'.$id, [
            ...$this->dados(),
            'titulo' => 'Canalizador editado',
        ], ['Accept' => 'application/json'])->assertOk()
            ->assertJsonPath('data.titulo', 'Canalizador editado');
    }

    public function test_meus_anuncios_incluem_rejeitados_com_motivo(): void
    {
        $user = User::factory()->create();
        $anuncio = Anuncio::factory()->create([
            'user_id' => $user->id,
            'status' => 'rejeitado',
            'motivo_rejeicao' => 'Conteúdo não permitido',
            'titulo' => 'Recusado',
        ]);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/me/anuncios')
            ->assertOk()
            ->assertJsonPath('data.0.id', $anuncio->id)
            ->assertJsonPath('data.0.status', 'rejeitado')
            ->assertJsonPath('data.0.motivo_rejeicao', 'Conteúdo não permitido');
    }

    public function test_nao_edita_anuncio_de_outro(): void
    {
        $dono = User::factory()->create();
        $outro = User::factory()->create();
        $anuncio = Anuncio::factory()->create(['user_id' => $dono->id]);
        Sanctum::actingAs($outro);

        $this->putJson('/api/v1/anuncios/'.$anuncio->id, $this->dados())->assertForbidden();
    }

    public function test_reativar_so_expirado_e_apagar(): void
    {
        $user = User::factory()->create();
        $ativo = Anuncio::factory()->create(['user_id' => $user->id, 'status' => 'aprovado']);
        $expirado = Anuncio::factory()->create(['user_id' => $user->id, 'status' => 'expirado']);
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/anuncios/'.$ativo->id.'/reativar')->assertStatus(400);
        $this->postJson('/api/v1/anuncios/'.$expirado->id.'/reativar')
            ->assertOk()
            ->assertJsonPath('data.status', 'aprovado');

        $this->deleteJson('/api/v1/anuncios/'.$ativo->id)->assertOk();
        $this->assertDatabaseMissing('anuncios', ['id' => $ativo->id]);
    }

    public function test_site_continua_a_criar_anuncio_com_sessao(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/anuncios', $this->dados())
            ->assertRedirect();

        $this->assertDatabaseHas('anuncios', [
            'user_id' => $user->id,
            'status' => 'pendente',
            'titulo' => 'Canalizador',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function dados(): array
    {
        return [
            'titulo' => 'Canalizador',
            'minha_empresa' => 'Ana Silva',
            'categoria_id' => Categoria::factory()->create()->id,
            'distrito_id' => Distrito::factory()->create()->id,
            'descricao' => 'Desentupimentos ao domicílio.',
            'contacto' => '912345678',
            'email' => 'ana@example.com',
        ];
    }
}
