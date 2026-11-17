<?php

namespace Tests\Feature\Api;

use App\Models\Anuncio;
use App\Models\Categoria;
use App\Models\Concelho;
use App\Models\Distrito;
use App\Models\GaleriaAnuncio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnuncioApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_lista_so_anuncios_aprovados_com_filtros_e_seed_estavel(): void
    {
        $user = User::factory()->create();
        $canalizacao = Categoria::factory()->create(['nome' => 'Canalização']);
        $pintura = Categoria::factory()->create(['nome' => 'Pintura']);
        $lisboa = Distrito::factory()->create(['nome' => 'Lisboa']);
        $porto = Distrito::factory()->create(['nome' => 'Porto']);
        $sintra = Concelho::factory()->create(['distrito_id' => $lisboa->id, 'nome' => 'Sintra']);
        $cascais = Concelho::factory()->create(['distrito_id' => $lisboa->id, 'nome' => 'Cascais']);

        $visivel = $this->anuncio($user, [
            'titulo' => 'Canalizador em Sintra',
            'descricao' => 'Desentupimentos urgentes',
            'categoria_id' => $canalizacao->id,
            'distrito_id' => $lisboa->id,
            'concelho_id' => $sintra->id,
            'status' => 'aprovado',
        ]);

        $this->anuncio($user, [
            'titulo' => 'Pintor em Cascais',
            'categoria_id' => $pintura->id,
            'distrito_id' => $lisboa->id,
            'concelho_id' => $cascais->id,
            'status' => 'aprovado',
        ]);

        $this->anuncio($user, [
            'titulo' => 'Eletricista no Porto',
            'categoria_id' => $canalizacao->id,
            'distrito_id' => $porto->id,
            'status' => 'aprovado',
        ]);

        foreach (['pendente', 'rejeitado', 'expirado', 'rascunho'] as $status) {
            $this->anuncio($user, [
                'titulo' => 'Escondido '.$status,
                'status' => $status,
                'categoria_id' => $canalizacao->id,
                'distrito_id' => $lisboa->id,
            ]);
        }

        $todos = $this->getJson('/api/v1/anuncios?seed=42')
            ->assertOk()
            ->assertJsonPath('seed', 42)
            ->assertJsonPath('meta.per_page', 12)
            ->assertJsonCount(3, 'data');

        $ids = $todos->json('data.*.id');
        $escondidos = Anuncio::query()->where('titulo', 'like', 'Escondido%')->pluck('id')->all();
        $this->assertNotEmpty($escondidos);
        $this->assertSame([], array_values(array_intersect($escondidos, $ids)));
        $this->assertSame($ids, $this->getJson('/api/v1/anuncios?seed=42')->json('data.*.id'));

        $this->getJson('/api/v1/anuncios?seed=42&categoria='.$canalizacao->id)
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $this->getJson('/api/v1/anuncios?seed=42&distrito='.$lisboa->id)
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $this->getJson('/api/v1/anuncios?seed=42&concelho_id='.$sintra->id)
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $visivel->id);

        $this->getJson('/api/v1/anuncios?seed=42&q=desentup')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $visivel->id);
    }

    public function test_paginacao_nao_repete_anuncios(): void
    {
        $user = User::factory()->create();

        for ($i = 1; $i <= 13; $i++) {
            $this->anuncio($user, ['titulo' => 'Servico '.$i, 'status' => 'aprovado']);
        }

        $pagina1 = $this->getJson('/api/v1/anuncios?seed=7')->assertOk()->json('data.*.id');
        $pagina2 = $this->getJson('/api/v1/anuncios?seed=7&page=2')->assertOk()->json('data.*.id');

        $this->assertCount(12, $pagina1);
        $this->assertCount(1, $pagina2);
        $this->assertSame([], array_intersect($pagina1, $pagina2));
    }

    public function test_sem_seed_a_resposta_traz_um_para_reutilizar(): void
    {
        $this->anuncio(User::factory()->create(), ['status' => 'aprovado']);

        $seed = $this->getJson('/api/v1/anuncios')->assertOk()->json('seed');

        $this->assertIsInt($seed);
        $this->assertGreaterThanOrEqual(1, $seed);
    }

    public function test_detalhe_de_anuncio_aprovado_com_imagem_absoluta(): void
    {
        $anuncio = $this->anuncio(User::factory()->create(), [
            'titulo' => 'Jardineiro',
            'status' => 'aprovado',
            'views' => 4,
        ]);

        GaleriaAnuncio::create([
            'anuncio_id' => $anuncio->id,
            'imagem' => 'anuncios/2026/11/capa.jpg',
            'ordem' => 1,
        ]);

        $this->getJson('/api/v1/anuncios/'.$anuncio->id)
            ->assertOk()
            ->assertJsonPath('data.titulo', 'Jardineiro')
            ->assertJsonPath('data.views', 5)
            ->assertJsonPath('data.imagem_capa', 'http://localhost/storage/anuncios/2026/11/capa.jpg')
            ->assertJsonPath('data.imagens.0.url', 'http://localhost/storage/anuncios/2026/11/capa.jpg')
            ->assertJsonMissingPath('data.contacto')
            ->assertJsonMissingPath('data.email');

        $this->assertSame(5, $anuncio->fresh()->views);

        $this->getJson('/api/v1/anuncios/'.$anuncio->slug)
            ->assertOk()
            ->assertJsonPath('data.id', $anuncio->id);
    }

    public function test_detalhe_pendente_fica_escondido_de_quem_nao_e_dono(): void
    {
        $dono = User::factory()->create();
        $outro = User::factory()->create();
        $anuncio = $this->anuncio($dono, ['status' => 'pendente', 'titulo' => 'Ainda nao']);

        $this->getJson('/api/v1/anuncios/'.$anuncio->id)->assertForbidden();
        $this->assertSame(0, $anuncio->fresh()->views);

        $this->withToken($outro->createToken('android')->plainTextToken)
            ->getJson('/api/v1/anuncios/'.$anuncio->id)
            ->assertForbidden();
    }

    public function test_detalhe_pendente_visivel_para_o_dono(): void
    {
        $dono = User::factory()->create();
        $anuncio = $this->anuncio($dono, ['status' => 'pendente', 'titulo' => 'Ainda nao']);

        $this->withToken($dono->createToken('android')->plainTextToken)
            ->getJson('/api/v1/anuncios/'.$anuncio->id)
            ->assertOk()
            ->assertJsonPath('data.titulo', 'Ainda nao');
    }

    public function test_anuncio_inexistente_devolve_json_404(): void
    {
        $this->get('/api/v1/anuncios/999999')
            ->assertNotFound()
            ->assertJsonStructure(['message']);
    }

    private function anuncio(User $user, array $attrs): Anuncio
    {
        return Anuncio::factory()->create(array_merge([
            'user_id' => $user->id,
            'status' => 'aprovado',
        ], $attrs));
    }
}
