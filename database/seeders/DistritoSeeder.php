<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DistritoSeeder extends Seeder
{
    /**
     * Real district list from the legacy TopCasa database, including the Açores/Madeira islands.
     */
    public function run(): void
    {
        $rows = [
            [1, 'Aveiro'],
            [2, 'Beja'],
            [3, 'Braga'],
            [4, 'Bragança'],
            [5, 'Castelo Branco'],
            [6, 'Coimbra'],
            [7, 'Évora'],
            [8, 'Faro'],
            [9, 'Guarda'],
            [10, 'Leiria'],
            [11, 'Lisboa'],
            [12, 'Portalegre'],
            [13, 'Porto'],
            [14, 'Santarém'],
            [15, 'Setúbal'],
            [16, 'Viana do Castelo'],
            [17, 'Vila Real'],
            [18, 'Viseu'],
            [19, 'Ilha da Madeira'],
            [20, 'Ilha de Porto Santo'],
            [21, 'Ilha de Santa Maria'],
            [22, 'Ilha de São Miguel'],
            [23, 'Ilha Terceira'],
            [24, 'Ilha Graciosa'],
            [25, 'Ilha de São Jorge'],
            [26, 'Ilha do Pico'],
            [27, 'Ilha do Faial'],
            [28, 'Ilha das Flores'],
            [29, 'Ilha do Corvo'],
        ];

        $now = now();
        DB::table('distritos')->insert(array_map(
            fn ($row) => ['id' => $row[0], 'nome' => $row[1], 'created_at' => $now, 'updated_at' => $now],
            $rows
        ));
    }
}
