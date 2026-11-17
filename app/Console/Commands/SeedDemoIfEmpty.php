<?php

namespace App\Console\Commands;

use App\Models\Categoria;
use Illuminate\Console\Command;

class SeedDemoIfEmpty extends Command
{
    protected $signature = 'app:seed-demo-if-empty';

    protected $description = 'Populate reference data + fictional demo content on first boot only (idempotent).';

    public function handle(): int
    {
        if (Categoria::count() > 0) {
            $this->info('Já há dados — a saltar o seeding.');

            return self::SUCCESS;
        }

        $this->call('db:seed', ['--force' => true]);
        $this->call('db:seed', ['--class' => \Database\Seeders\DemoSeeder::class, '--force' => true]);

        $this->info('Seeding de demonstração concluído.');

        return self::SUCCESS;
    }
}
