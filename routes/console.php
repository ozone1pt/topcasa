<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// A single cron entry on the server (`* * * * * php artisan schedule:run`)
// drives everything below - see Fase 12 for the actual crontab setup.
Schedule::command('app:expirar-anuncios')->dailyAt('02:00');
Schedule::command('app:avisar-expiracao-anuncios')->dailyAt('08:00');
Schedule::command('app:lembrar-utilizadores-sem-anuncios')->dailyAt('09:00');
Schedule::command('app:gerar-artigo-blog')->dailyAt('06:00');
Schedule::command('app:gerar-glossario-categoria')->weeklyOn(1, '03:00');
