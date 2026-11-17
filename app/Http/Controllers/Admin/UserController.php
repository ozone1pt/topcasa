<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ImageUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(private readonly ImageUploadService $images)
    {
    }

    public function index(Request $request): View
    {
        $users = User::query()
            ->withCount('anuncios')
            ->when($request->filled('busca'), function ($q) use ($request) {
                $termo = '%'.$request->string('busca').'%';
                $q->where(fn ($q) => $q->where('username', 'like', $termo)->orWhere('email', 'like', $termo)->orWhere('nome', 'like', $termo));
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', ['users' => $users]);
    }

    /**
     * The "ficha" - same fields the public profile/registration already
     * uses on the User model, plus what's derived from it: the professional's
     * anúncios and the contacts those anúncios have generated.
     */
    public function show(User $user): View
    {
        $user->load(['anuncios' => fn ($q) => $q->with('categoria')->latest()]);

        $contactos = [
            'phone_clicks' => (int) $user->anuncios->sum('phone_clicks'),
            'whatsapp_clicks' => (int) $user->anuncios->sum('whatsapp_clicks'),
            'email_clicks' => (int) $user->anuncios->sum('email_clicks'),
            'message_count' => (int) $user->anuncios->sum('message_count'),
            'views' => (int) $user->anuncios->sum('views'),
        ];

        return view('admin.users.show', ['user' => $user, 'contactos' => $contactos]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:100'],
            'apelido' => ['nullable', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'telefone' => ['nullable', 'string', 'max:30'],
            'nif' => ['nullable', 'string', 'max:9'],
            'tipo_conta' => ['nullable', 'in:particular,profissional,empresa'],
            'empresa_nome' => ['nullable', 'string', 'max:160'],
            'notificar_orcamentos' => ['sometimes', 'boolean'],
            'notificar_recrutamento' => ['sometimes', 'boolean'],
        ]);

        $user->update([
            ...$data,
            'notificar_orcamentos' => $request->boolean('notificar_orcamentos'),
            'notificar_recrutamento' => $request->boolean('notificar_recrutamento'),
        ]);

        return back()->with('status', 'Ficha atualizada.');
    }

    public function aprovar(User $user): RedirectResponse
    {
        $user->update(['status' => 'aprovado']);

        return back()->with('status', 'Utilizador aprovado.');
    }

    public function rejeitar(User $user): RedirectResponse
    {
        $user->update(['status' => 'rejeitado']);

        return back()->with('status', 'Utilizador rejeitado.');
    }

    /**
     * Deleting cascades at the DB level (anuncios, mensagens, favoritos,
     * reviews, alertas all have cascadeOnDelete on user_id) - much simpler
     * than the legacy's manual multi-table transaction. We still need to
     * remove the physical uploaded files ourselves first.
     */
    public function destroy(User $user): RedirectResponse
    {
        foreach ($user->anuncios()->with('galeria')->get() as $anuncio) {
            foreach ($anuncio->galeria as $imagem) {
                $this->images->delete($imagem->imagem);
            }
        }

        $user->delete();

        return back()->with('status', 'Utilizador removido.');
    }
}
