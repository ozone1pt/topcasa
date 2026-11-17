<?php

namespace Tests\Feature;

use Illuminate\Contracts\Console\Kernel;
use Tests\TestCase;

class PublicPathTest extends TestCase
{
    public function test_app_public_path_passa_a_valer_depois_de_carregar_o_env(): void
    {
        $destino = sys_get_temp_dir().'/topcasa-public-'.uniqid();
        mkdir($destino);

        $this->definirPublicPath($destino);

        try {
            $app = require base_path('bootstrap/app.php');
            $app->make(Kernel::class)->bootstrap();

            $this->assertSame(
                rtrim($destino, DIRECTORY_SEPARATOR),
                rtrim($app->publicPath(), DIRECTORY_SEPARATOR),
            );
        } finally {
            $this->definirPublicPath(null);
            rmdir($destino);
        }
    }

    public function test_sem_app_public_path_mantem_a_pasta_acima_do_projeto(): void
    {
        $this->definirPublicPath(null);

        $app = require base_path('bootstrap/app.php');
        $app->make(Kernel::class)->bootstrap();

        $this->assertSame(
            rtrim(dirname(base_path()), DIRECTORY_SEPARATOR),
            rtrim($app->publicPath(), DIRECTORY_SEPARATOR),
        );
    }

    private function definirPublicPath(?string $caminho): void
    {
        if ($caminho === null) {
            putenv('APP_PUBLIC_PATH');
            unset($_ENV['APP_PUBLIC_PATH'], $_SERVER['APP_PUBLIC_PATH']);

            return;
        }

        putenv('APP_PUBLIC_PATH='.$caminho);
        $_ENV['APP_PUBLIC_PATH'] = $caminho;
        $_SERVER['APP_PUBLIC_PATH'] = $caminho;
    }
}
