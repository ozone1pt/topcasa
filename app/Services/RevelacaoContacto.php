<?php

namespace App\Services;

use App\Models\Anuncio;
use App\Models\ContactoEvento;

class RevelacaoContacto
{
    public function __construct(private readonly ContactoReveladoNotifier $contactoNotifier) {}

    public function telefone(Anuncio $anuncio, string $ip): string
    {
        $anuncio->increment('phone_clicks');
        ContactoEvento::registar('telefone', $anuncio);

        // Mail goes out after the response so a slow server never hides the number.
        app()->terminating(fn () => $this->contactoNotifier->notificar($anuncio, $ip));

        return (string) $anuncio->contacto;
    }

    public function whatsapp(Anuncio $anuncio): string
    {
        $url = $anuncio->whatsappUrl();
        abort_unless($url, 404);

        $anuncio->increment('whatsapp_clicks');
        ContactoEvento::registar('whatsapp', $anuncio);

        return $url;
    }
}
