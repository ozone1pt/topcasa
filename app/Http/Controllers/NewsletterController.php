<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'nome' => ['nullable', 'string', 'max:100'],
        ]);

        NewsletterSubscriber::updateOrCreate(
            ['email' => $data['email']],
            ['nome' => $data['nome'] ?? null, 'ativo' => true]
        );

        return back()->with('status', 'Subscrição confirmada!');
    }

    /**
     * Reached via a signed URL emailed to the subscriber (see EmailService
     * equivalent Fase 7 campaigns), so there's no ID-enumeration risk.
     */
    public function destroy(Request $request): RedirectResponse
    {
        abort_unless($request->hasValidSignature(), 403);

        NewsletterSubscriber::where('email', $request->query('email'))->update(['ativo' => false]);

        return redirect()->route('anuncios.index')->with('status', 'Subscrição cancelada.');
    }
}
