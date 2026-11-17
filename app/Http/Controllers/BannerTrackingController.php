<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

class BannerTrackingController extends Controller
{
    private const PIXEL = "\x47\x49\x46\x38\x39\x61\x01\x00\x01\x00\x80\x00\x00\x00\x00\x00\xFF\xFF\xFF\x21\xF9\x04\x01\x00\x00\x00\x00\x2C\x00\x00\x00\x00\x01\x00\x01\x00\x00\x02\x02\x44\x01\x00\x3B";

    public function view(Banner $banner): Response
    {
        $banner->increment('views');

        return response(self::PIXEL, 200, ['Content-Type' => 'image/gif']);
    }

    public function click(Banner $banner): RedirectResponse
    {
        $banner->increment('clicks');

        return redirect($banner->url_destino ?: route('anuncios.index'));
    }
}
