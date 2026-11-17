<?php

use App\Http\Middleware\AutenticacaoOpcional;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        // api/v1 keeps the existing web route GET /api/distritos/{id}/concelhos working.
        apiPrefix: 'api/v1',
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

        // Named limiter "api" (defined in AppServiceProvider). Auth routes add
        // a stricter "auth" limiter on top.
        $middleware->throttleApi();

        $middleware->alias([
            'auth.optional' => AutenticacaoOpcional::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // The mobile client does not send browser Accept headers reliably.
        // Anything under /api (v1 and the legacy concelhos endpoint) is JSON.
        $exceptions->shouldRenderJsonWhen(function (Request $request, Throwable $e) {
            return $request->is('api/*') || $request->expectsJson();
        });
    })->create();

// Production keeps the Hostinger layout: the app lives in a protected folder
// one level above the real document root, and public/ was copied into that
// docroot. CLI commands (config:cache, package:discover) never run index.php,
// which is the only place staging calls usePublicPath(), so without an
// override they would point the public disk at the production docroot.
// APP_PUBLIC_PATH, when set, wins for both HTTP and CLI. Unset, behaviour
// stays as before — including a staging index.php that sets the path itself.
$app->usePublicPath(dirname(__DIR__, 2));

$app->afterLoadingEnvironment(function () use ($app) {
    $configured = $_ENV['APP_PUBLIC_PATH'] ?? $_SERVER['APP_PUBLIC_PATH'] ?? getenv('APP_PUBLIC_PATH');

    if (is_string($configured) && $configured !== '') {
        $app->usePublicPath($configured);
    }
});

return $app;
