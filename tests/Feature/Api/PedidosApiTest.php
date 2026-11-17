<?php

namespace Tests\Feature\Api;

use App\Models\Anuncio;
use App\Models\BlogPost;
use App\Models\Distrito;
use App\Models\PedidoOrcamento;
use App\Models\PedidoRecrutamento;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PedidosApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_orcamentos_publicos_so_validados_e_o_contacto_fica_escondido(): void
    {
        $visivel = PedidoOrcamento::factory()->create(['status' => 'validado', 'titulo' => 'Pintar sala']);
        PedidoOrcamento::factory()->create(['status' => 'pendente', 'titulo' => 'Escondido']);
        PedidoOrcamento::factory()->create(['status' => 'rejeitado', 'titulo' => 'Recusado']);

        $this->getJson('/api/v1/orcamentos')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $visivel->id)
            ->assertJsonMissingPath('data.0.telefone')
            ->assertJsonMissingPath('data.0.email');

        $this->postJson('/api/v1/orcamentos/'.$visivel->id.'/desbloquear')
            ->assertOk()
            ->assertJsonPath('telefone', $visivel->telefone);

        $this->assertSame(1, $visivel->fresh()->contacto_clicks);

        $pendente = PedidoOrcamento::factory()->create(['status' => 'pendente']);
        $this->postJson('/api/v1/orcamentos/'.$pendente->id.'/desbloquear')->assertNotFound();
    }

    public function test_criar_orcamento_exige_sessao_fica_pendente_e_nao_envia_email(): void
    {
        Notification::fake();
        $this->postJson('/api/v1/orcamentos', $this->orcamento())->assertUnauthorized();

        $user = User::factory()->create();
        Sanctum::actingAs($user);
        Storage::fake('public');

        $this->post('/api/v1/orcamentos', [
            ...$this->orcamento(),
            'foto' => UploadedFile::fake()->image('pedido.jpg'),
        ], ['Accept' => 'application/json'])
            ->assertCreated()
            ->assertJsonPath('data.status', 'pendente')
            ->assertJsonPath('data.telefone', '913333333');

        Notification::assertNothingSent();

        $this->postJson('/api/v1/orcamentos', $this->orcamento())
            ->assertUnprocessable()
            ->assertJsonValidationErrors('telefone');
    }

    public function test_dono_conclui_so_um_orcamento_validado(): void
    {
        $dono = User::factory()->create();
        $outro = User::factory()->create();
        $pedido = PedidoOrcamento::factory()->create(['user_id' => $dono->id, 'status' => 'pendente']);
        Sanctum::actingAs($dono);

        $this->getJson('/api/v1/me/orcamentos')
            ->assertOk()
            ->assertJsonPath('data.0.status', 'pendente');

        $this->postJson('/api/v1/orcamentos/'.$pedido->id.'/concluir')->assertNotFound();

        $pedido->update(['status' => 'validado']);
        Sanctum::actingAs($outro);
        $this->postJson('/api/v1/orcamentos/'.$pedido->id.'/concluir')->assertForbidden();

        Sanctum::actingAs($dono);
        $this->postJson('/api/v1/orcamentos/'.$pedido->id.'/concluir')
            ->assertOk()
            ->assertJsonPath('data.status', 'concluido');
    }

    public function test_recrutamento_publico_so_ativo_e_o_dono_gere_o_seu(): void
    {
        Notification::fake();
        $dono = User::factory()->create();
        PedidoRecrutamento::factory()->create(['status' => 'ativo', 'titulo' => 'Canalizador']);
        PedidoRecrutamento::factory()->create(['status' => 'pausado', 'titulo' => 'Pausada']);
        PedidoRecrutamento::factory()->create(['status' => 'pendente', 'titulo' => 'Nova']);

        $this->getJson('/api/v1/recrutamento')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.titulo', 'Canalizador')
            ->assertJsonMissingPath('data.0.status');

        $this->postJson('/api/v1/recrutamento', $this->recrutamento())->assertUnauthorized();

        Sanctum::actingAs($dono);
        $id = $this->postJson('/api/v1/recrutamento', $this->recrutamento())
            ->assertCreated()
            ->assertJsonPath('data.status', 'pendente')
            ->json('data.id');

        Notification::assertNothingSent();

        $this->postJson('/api/v1/recrutamento/'.$id.'/pausar')->assertNotFound();

        $aprovada = PedidoRecrutamento::factory()->create(['user_id' => $dono->id, 'status' => 'ativo']);
        $this->postJson('/api/v1/recrutamento/'.$aprovada->id.'/pausar')
            ->assertOk()
            ->assertJsonPath('data.status', 'pausado');
        $this->postJson('/api/v1/recrutamento/'.$aprovada->id.'/pausar')
            ->assertOk()
            ->assertJsonPath('data.status', 'ativo');

        $this->deleteJson('/api/v1/recrutamento/'.$aprovada->id)->assertOk();
        $this->assertDatabaseMissing('pedidos_recrutamento', ['id' => $aprovada->id]);
    }

    public function test_resumo_reviews_password_conta_blog_e_newsletter(): void
    {
        $dono = User::factory()->create(['password' => Hash::make('password')]);
        $cliente = User::factory()->create();
        Anuncio::factory()->create(['user_id' => $dono->id, 'status' => 'aprovado']);
        $anuncio = Anuncio::factory()->create(['user_id' => $dono->id, 'status' => 'aprovado', 'titulo' => 'Serviço']);
        Review::create([
            'anuncio_id' => $anuncio->id,
            'user_id' => $cliente->id,
            'anunciante_id' => $dono->id,
            'rating' => 5,
            'comment' => 'Excelente',
            'status' => 'pendente',
        ]);

        Sanctum::actingAs($dono);
        $this->getJson('/api/v1/me/resumo')
            ->assertOk()
            ->assertJsonPath('data.anuncios.aprovado', 2)
            ->assertJsonPath('data.anuncios.total', 2)
            ->assertJsonPath('data.reviews_por_moderar', 1);

        $this->getJson('/api/v1/me/reviews')
            ->assertOk()
            ->assertJsonPath('data.0.comment', 'Excelente')
            ->assertJsonPath('data.0.anuncio_titulo', 'Serviço');

        $this->putJson('/api/v1/me/password', [
            'current_password' => 'password',
            'password' => 'nova-password',
            'password_confirmation' => 'nova-password',
        ])->assertOk();

        $this->assertTrue(Hash::check('nova-password', $dono->fresh()->password));

        $post = BlogPost::factory()->create(['titulo' => 'Como escolher um canalizador', 'status' => 'publicado']);
        BlogPost::factory()->create(['status' => 'pendente', 'titulo' => 'Rascunho secreto']);

        $this->getJson('/api/v1/blog')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.titulo', 'Como escolher um canalizador');

        $this->getJson('/api/v1/blog/'.$post->slug)->assertOk()->assertJsonPath('data.titulo', $post->titulo);
        $this->getJson('/api/v1/blog/nao-existe')->assertNotFound();

        $this->postJson('/api/v1/newsletter', ['email' => 'leitor@example.com', 'nome' => 'Leitor'])
            ->assertOk();
        $this->assertDatabaseHas('newsletter_subscribers', ['email' => 'leitor@example.com', 'ativo' => true]);

        $alerta = $dono->alertas()->create([
            'titulo' => 'Aviso',
            'mensagem' => 'Texto',
            'tipo' => 'info',
        ]);
        $this->postJson('/api/v1/alertas/lidos')->assertOk()->assertJsonPath('total', 1);
        $this->assertTrue($alerta->fresh()->lida);

        $this->deleteJson('/api/v1/me', ['password' => 'nova-password'])->assertOk();
        $this->assertDatabaseMissing('users', ['id' => $dono->id]);
    }

    /**
     * @return array<string, mixed>
     */
    private function orcamento(): array
    {
        return [
            'titulo' => 'Reparar telhado',
            'nome' => 'Ana',
            'telefone' => '913333333',
            'email' => 'ana@example.com',
            'distrito_id' => Distrito::factory()->create()->id,
            'descricao' => 'Telhas partidas depois da chuva.',
            'termo_aceite' => true,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function recrutamento(): array
    {
        return [
            'titulo' => 'Procura-se eletricista',
            'empresa_nome' => 'Casa Norte',
            'email' => 'empresa@example.com',
            'telefone' => '914444444',
            'distrito_id' => Distrito::factory()->create()->id,
            'descricao' => 'Trabalho a tempo inteiro.',
            'termo_aceite' => true,
        ];
    }
}
