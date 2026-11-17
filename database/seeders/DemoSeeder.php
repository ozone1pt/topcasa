<?php

namespace Database\Seeders;

use App\Models\Anuncio;
use App\Models\BlogPost;
use App\Models\Categoria;
use App\Models\Concelho;
use App\Models\Distrito;
use App\Models\PedidoOrcamento;
use App\Models\PedidoRecrutamento;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Fictional demo data for temporary preview deployments (no real customer PII).
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = Categoria::all();
        $distritos = Distrito::with('concelhos')->get();

        $profissionais = User::factory(15)->create([
            'user_type' => 'gestor',
            'status' => 'aprovado',
        ]);

        $anuncios = collect();

        foreach (range(1, 40) as $i) {
            $distrito = $distritos->random();
            $concelho = $distrito->concelhos->isNotEmpty()
                ? $distrito->concelhos->random()
                : Concelho::factory()->create(['distrito_id' => $distrito->id]);

            $anuncios->push(Anuncio::factory()->create([
                'user_id' => $profissionais->random()->id,
                'categoria_id' => $categorias->random()->id,
                'distrito_id' => $distrito->id,
                'concelho_id' => $concelho->id,
                'destacar' => $i <= 6,
            ]));
        }

        foreach ($anuncios->random(15) as $anuncio) {
            Review::factory()->create([
                'anuncio_id' => $anuncio->id,
                'anunciante_id' => $anuncio->user_id,
                'status' => 'aprovado',
            ]);
        }

        foreach (range(1, 8) as $i) {
            PedidoOrcamento::factory()->create([
                'distrito_id' => $distritos->random()->id,
            ]);
        }

        foreach (range(1, 5) as $i) {
            PedidoRecrutamento::factory()->create([
                'distrito_id' => $distritos->random()->id,
            ]);
        }

        BlogPost::factory(6)->create();
    }
}
