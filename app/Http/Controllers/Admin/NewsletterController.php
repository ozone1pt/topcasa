<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\EnviarCampanhaNewsletter;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NewsletterController extends Controller
{
    public function index(): View
    {
        return view('admin.newsletter.index', [
            'subscribers' => NewsletterSubscriber::orderByDesc('created_at')->paginate(30),
            'totalAtivos' => NewsletterSubscriber::where('ativo', true)->count(),
        ]);
    }

    public function enviar(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'assunto' => ['required', 'string', 'max:255'],
            'corpo' => ['required', 'string'],
        ]);

        EnviarCampanhaNewsletter::dispatch($data['assunto'], $data['corpo']);

        return back()->with('status', 'Campanha em envio para os subscritores ativos.');
    }
}
