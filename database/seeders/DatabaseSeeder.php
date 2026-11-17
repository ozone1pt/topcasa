<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            DistritoSeeder::class,
            ConcelhoSeeder::class,
            CategoriaSeeder::class,
        ]);

        User::factory()->create([
            'username' => 'admin',
            'email' => 'admin@topcasa.pt',
            'nome' => 'Admin',
            'apelido' => 'TopCasa',
            'user_type' => 'admin',
            'status' => 'aprovado',
        ]);
    }
}
