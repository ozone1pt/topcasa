<?php

namespace Tests\Feature\Api;

use App\Models\Categoria;
use App\Models\Concelho;
use App\Models\Distrito;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogoApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_lista_distritos_por_nome(): void
    {
        Distrito::factory()->create(['nome' => 'Porto']);
        Distrito::factory()->create(['nome' => 'Aveiro']);

        $this->getJson('/api/v1/distritos')
            ->assertOk()
            ->assertJsonPath('data.0.nome', 'Aveiro')
            ->assertJsonPath('data.1.nome', 'Porto');
    }

    public function test_concelhos_de_um_distrito(): void
    {
        $lisboa = Distrito::factory()->create(['nome' => 'Lisboa']);
        $porto = Distrito::factory()->create(['nome' => 'Porto']);
        Concelho::factory()->create(['distrito_id' => $lisboa->id, 'nome' => 'Sintra']);
        Concelho::factory()->create(['distrito_id' => $lisboa->id, 'nome' => 'Cascais']);
        Concelho::factory()->create(['distrito_id' => $porto->id, 'nome' => 'Gaia']);

        $this->getJson('/api/v1/distritos/'.$lisboa->id.'/concelhos')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.nome', 'Cascais')
            ->assertJsonPath('data.1.nome', 'Sintra');
    }

    public function test_rota_web_de_concelhos_continua_a_devolver_a_lista_simples(): void
    {
        $lisboa = Distrito::factory()->create();
        Concelho::factory()->create(['distrito_id' => $lisboa->id, 'nome' => 'Sintra']);

        $this->getJson('/api/distritos/'.$lisboa->id.'/concelhos')
            ->assertOk()
            ->assertJsonPath('0.nome', 'Sintra')
            ->assertJsonMissingPath('data');
    }

    public function test_categorias_so_as_ativas(): void
    {
        Categoria::factory()->create(['nome' => 'Inativa', 'status' => false]);
        Categoria::factory()->create(['nome' => 'Canalização', 'status' => true, 'icone' => 'fa-wrench']);

        $this->getJson('/api/v1/categorias')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.nome', 'Canalização')
            ->assertJsonPath('data.0.icone', 'fa-wrench');
    }
}
