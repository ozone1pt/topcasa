<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriaSeeder extends Seeder
{
    /**
     * Real category list from the legacy TopCasa database. [id, nome, icone (Font Awesome class), status]
     * IDs are not contiguous (gaps at 6-8, 42) because some legacy categories were deleted over time.
     */
    public function run(): void
    {
        $rows = [
            [1, 'Electricista', 'fa-bolt', true],
            [2, 'Canalizador', 'fa-faucet', true],
            [3, 'Pavimentos', 'fa-th', true],
            [4, 'Pedreiro', 'fa-hammer', true],
            [5, 'Pintor', 'fa-paint-brush', true],
            [9, 'Climatização', 'fa-snowflake', true],
            [10, 'Serralheiro', 'fa-key', true],
            [11, 'Pladur', 'fa-tools', true],
            [12, 'Telhados', 'fa-house', true],
            [13, 'Isolamentos', 'fa-check', true],
            [14, 'Carpintaria', 'fa-hammer', true],
            [15, 'Portões', 'fa-door-open', true],
            [16, 'Caixilharia', 'fa-tools', true],
            [17, 'Construtores', 'fa-hard-hat', true],
            [18, 'Jardinagem', 'fa-tree', true],
            [19, 'Limpeza terrenos', 'fa-leaf', true],
            [20, 'Montagem Móveis', 'fa-tools', true],
            [21, 'Reparação Electrodomésticos', 'fa-screwdriver', true],
            [22, 'Fachadas', 'fa-paint-brush', true],
            [23, 'Ladrilhador', 'fa-hammer', true],
            [24, 'Arquitetos', 'fa-home', true],
            [25, 'Decoração', 'fa-heart', true],
            [26, 'Vigilância', 'fa-spray-can', true],
            [27, 'Cozinhas', 'fa-store', true],
            [28, 'Paineis Solares', 'fa-battery-full', true],
            [29, 'PetSitting', 'fa-heart', true],
            [30, 'Piscinas', 'fa-water', true],
            [31, 'Aluguer', 'fa-wrench', true],
            [32, 'Armador', 'fa-building', true],
            [33, 'Materiais', 'fa-gear', true],
            [34, 'Calceteiro', 'fa-cog', true],
            [35, 'Ferramentas', 'fa-tools', true],
            [36, 'Revestimentos', 'fa-home', true],
            [37, 'Certificados', null, true],
            [38, 'Transportes', 'fa-cog', true],
            [39, 'Entulhos', null, true],
            [40, 'LSF', 'fa-house', true],
            [41, 'Mudanças', 'fa-house', true],
            [43, 'Móveis Medida', 'fa-house', true],
            [44, 'Soldador', 'fa-tools', true],
            [45, 'Modulares', 'fa-home', true],
            [46, 'Remodelacoes', 'fa-home', true],
            [47, 'Limpezas Domésticas', 'fa-house', true],
            [48, 'Casas Madeira', 'fa-house', true],
            [49, 'WC', 'fa-bath', true],
            [50, 'Manutenção', 'fa-tools', true],
        ];

        $now = now();
        DB::table('categorias')->insert(array_map(
            fn ($row) => [
                'id' => $row[0],
                'nome' => $row[1],
                'icone' => $row[2],
                'status' => $row[3],
                'created_at' => $now,
                'updated_at' => $now,
            ],
            $rows
        ));
    }
}
