<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        $app = parent::createApplication();

        // bootstrap/app.php points public_path() at the Hostinger docroot, one
        // level above this repo. Views and Vite in tests use the repo's public/.
        $app->usePublicPath(dirname(__DIR__).'/public');

        return $app;
    }
}
