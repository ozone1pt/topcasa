<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Railway (and similar PaaS) terminate TLS at their edge proxy and forward
        // requests to the app over plain HTTP, so without this Laravel thinks every
        // request is insecure and generates http:// asset URLs, which browsers then
        // block as mixed content on the https:// page.
        $middleware->trustProxies(at: '*', headers: Request::HEADER_X_FORWARDED_FOR
            | Request::HEADER_X_FORWARDED_HOST
            | Request::HEADER_X_FORWARDED_PORT
            | Request::HEADER_X_FORWARDED_PROTO);

        // A guest hitting a /crm/* page gets bounced to the CRM-branded login
        // form instead of the public one - everything else keeps the
        // regular /login.
        $middleware->redirectGuestsTo(fn (Request $request) => $request->is('crm', 'crm/*')
            ? route('crm.login')
            : route('login'));

        // The public "Ver contacto" / WhatsApp / orçamento-contacto buttons are
        // anonymous one-click actions. On phones the page often sits in a
        // background tab until the session (and its CSRF token) has expired,
        // which made these buttons silently do nothing (419). They don't act
        // on a logged-in user's behalf, so a CSRF token adds no protection.
        $middleware->validateCsrfTokens(except: [
            'anuncios/*/revelar-telefone',
            'anuncios/*/revelar-whatsapp',
            'orcamentos/*/desbloquear',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

// On this Hostinger deployment the app lives in a protected "topcasa-app"
// folder one level above the real document root (public_html), and the
// contents of Laravel's public/ folder were moved directly into
// public_html so the site can be served from the domain root without SSH
// access to change the doc root. Point Laravel at the real public path so
// public_path() (Vite manifest, asset(), etc.) resolves correctly.
$app->usePublicPath(dirname(__DIR__, 2));

return $app;
