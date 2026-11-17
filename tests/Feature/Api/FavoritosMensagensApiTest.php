<?php

namespace Tests\Feature\Api;

use App\Models\Alerta;
use App\Models\Anuncio;
use App\Models\Mensagem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FavoritosMensagensApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_favoritos_alternar_e_listar(): void
    {
        $user = User::factory()->create();
        $anuncio = Anuncio::factory()->create(['status' => 'aprovado', 'titulo' => 'Pintor']);
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/anuncios/'.$anuncio->id.'/favorito')
            ->assertOk()
            ->assertJsonPath('favorito', true);

        $this->getJson('/api/v1/favoritos')
            ->assertOk()
            ->assertJsonPath('data.0.id', $anuncio->id);

        $this->postJson('/api/v1/anuncios/'.$anuncio->id.'/favorito')
            ->assertOk()
            ->assertJsonPath('favorito', false);

        $this->getJson('/api/v1/favoritos')->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_revelar_telefone_e_whatsapp(): void
    {
        $anuncio = Anuncio::factory()->create([
            'status' => 'aprovado',
            'contacto' => '912345678',
            'email' => 'dono@example.com',
        ]);

        $this->postJson('/api/v1/anuncios/'.$anuncio->id.'/revelar-telefone')
            ->assertOk()
            ->assertJsonPath('contacto', '912345678');

        $this->assertSame(1, $anuncio->fresh()->phone_clicks);
        $this->assertDatabaseHas('contacto_eventos', [
            'anuncio_id' => $anuncio->id,
            'origem' => 'telefone',
        ]);

        $this->postJson('/api/v1/anuncios/'.$anuncio->id.'/revelar-whatsapp')
            ->assertOk()
            ->assertJsonStructure(['url']);

        $this->assertSame(1, $anuncio->fresh()->whatsapp_clicks);
    }

    public function test_mensagens_caixa_resposta_e_apagada(): void
    {
        $dono = User::factory()->create(['nome' => 'Dono']);
        $cliente = User::factory()->create(['nome' => 'Cliente']);
        $anuncio = Anuncio::factory()->create([
            'user_id' => $dono->id,
            'status' => 'aprovado',
            'titulo' => 'Eletricista',
        ]);

        Sanctum::actingAs($cliente);
        $enviada = $this->postJson('/api/v1/anuncios/'.$anuncio->id.'/mensagens', [
            'mensagem' => 'Olá, está disponível?',
        ])->assertCreated();

        $id = $enviada->json('data.id');
        $this->assertSame(1, $anuncio->fresh()->message_count);

        Sanctum::actingAs($dono);
        $this->getJson('/api/v1/mensagens')
            ->assertOk()
            ->assertJsonPath('nao_lidas', 1)
            ->assertJsonPath('data.0.id', $id);

        $this->getJson('/api/v1/mensagens/'.$id)
            ->assertOk()
            ->assertJsonPath('data.lida', true);

        $this->postJson('/api/v1/mensagens/'.$id.'/responder', [
            'mensagem' => 'Sim, amanhã.',
        ])->assertCreated();

        $this->getJson('/api/v1/mensagens/'.$id)
            ->assertOk()
            ->assertJsonPath('data.respostas.0.mensagem', 'Sim, amanhã.');

        $this->deleteJson('/api/v1/mensagens/'.$id)->assertOk();

        $this->getJson('/api/v1/mensagens')->assertOk()->assertJsonCount(0, 'data');

        Sanctum::actingAs($cliente);
        $this->getJson('/api/v1/mensagens')
            ->assertOk()
            ->assertJsonPath('data.0.id', $id);
    }

    public function test_dono_pode_escrever_no_proprio_anuncio_e_a_conversa_desaparece_ao_apagar(): void
    {
        $dono = User::factory()->create();
        $anuncio = Anuncio::factory()->create([
            'user_id' => $dono->id,
            'status' => 'aprovado',
        ]);
        Sanctum::actingAs($dono);

        $id = $this->postJson('/api/v1/anuncios/'.$anuncio->id.'/mensagens', [
            'mensagem' => 'Nota para mim.',
        ])->assertCreated()->json('data.id');

        $this->assertDatabaseHas('mensagens', [
            'id' => $id,
            'remetente_id' => $dono->id,
            'destinatario_id' => $dono->id,
        ]);

        $this->deleteJson('/api/v1/mensagens/'.$id)->assertOk();

        $this->getJson('/api/v1/mensagens')->assertOk()->assertJsonCount(0, 'data');
        $this->assertNotNull(Mensagem::find($id)->remetente_deleted_at);
        $this->assertNotNull(Mensagem::find($id)->destinatario_deleted_at);
    }

    public function test_avaliacoes_e_alertas(): void
    {
        $dono = User::factory()->create(['nome' => 'Dono']);
        $cliente = User::factory()->create(['nome' => 'Cliente']);
        $anuncio = Anuncio::factory()->create(['user_id' => $dono->id, 'status' => 'aprovado']);

        Sanctum::actingAs($dono);
        $this->postJson('/api/v1/anuncios/'.$anuncio->id.'/reviews', [
            'rating' => 5,
        ])->assertUnprocessable()->assertJsonValidationErrors('rating');

        Sanctum::actingAs($cliente);
        $review = $this->postJson('/api/v1/anuncios/'.$anuncio->id.'/reviews', [
            'rating' => 4,
            'comment' => 'Bom trabalho',
        ])->assertCreated()->json('data.id');

        $this->postJson('/api/v1/anuncios/'.$anuncio->id.'/reviews', [
            'rating' => 3,
        ])->assertUnprocessable();

        $this->postJson('/api/v1/reviews/'.$review.'/aprovar')->assertForbidden();

        Sanctum::actingAs($dono);
        $this->postJson('/api/v1/reviews/'.$review.'/aprovar')
            ->assertOk()
            ->assertJsonPath('data.status', 'aprovado');

        $this->getJson('/api/v1/anuncios/'.$anuncio->id)
            ->assertOk()
            ->assertJsonPath('data.reviews.0.comment', 'Bom trabalho');

        $alerta = Alerta::query()->create([
            'user_id' => $dono->id,
            'titulo' => 'Novidade',
            'mensagem' => 'O teu anúncio foi visto.',
            'tipo' => 'info',
        ]);

        $this->getJson('/api/v1/alertas')
            ->assertOk()
            ->assertJsonPath('nao_lidas', 1)
            ->assertJsonPath('data.0.titulo', 'Novidade');

        $this->postJson('/api/v1/alertas/'.$alerta->id.'/lido')
            ->assertOk()
            ->assertJsonPath('data.lida', true);

        Sanctum::actingAs($cliente);
        $this->postJson('/api/v1/alertas/'.$alerta->id.'/lido')->assertForbidden();
    }
}
