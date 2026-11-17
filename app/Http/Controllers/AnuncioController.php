<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAnuncioRequest;
use App\Http\Requests\UpdateAnuncioRequest;
use App\Models\Anuncio;
use App\Models\Categoria;
use App\Models\Distrito;
use App\Models\GaleriaAnuncio;
use App\Models\Configuracao;
use App\Models\User;
use App\Notifications\AccountAutoCreatedNotification;
use App\Services\AnuncioPublicadoNotifier;
use App\Services\ContactoReveladoNotifier;
use App\Services\ImageUploadService;
use App\Services\ModeracaoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AnuncioController extends Controller
{
    public function __construct(
        private readonly ImageUploadService $images,
        private readonly ModeracaoService $moderacao,
        private readonly ContactoReveladoNotifier $contactoNotifier,
        private readonly AnuncioPublicadoNotifier $publicadoNotifier,
    ) {
    }

    public function index(Request $request): View
    {
        // The list is reshuffled every time somebody opens it (so the older
        // anúncios also get their turn at the top instead of being buried under
        // the newest ones). The shuffle is seeded and kept in the visitor's
        // session, so paging through the list (?page=2, 3...) keeps one
        // consistent order with no repeated or missing anúncios.
        if (! $request->has('page') || ! $request->session()->has('lista_seed')) {
            $request->session()->put('lista_seed', random_int(1, 999999));
        }

        $anuncios = Anuncio::query()
            ->publicados()
            ->with(['categoria', 'distrito', 'concelho', 'galeria', 'user'])
            ->when($request->filled('categoria_id'), fn ($q) => $q->where('categoria_id', $request->integer('categoria_id')))
            ->when($request->filled('distrito_id'), fn ($q) => $q->where('distrito_id', $request->integer('distrito_id')))
            ->when($request->filled('concelho_id'), fn ($q) => $q->where('concelho_id', $request->integer('concelho_id')))
            ->when($request->filled('busca'), function ($q) use ($request) {
                $termo = '%'.$request->string('busca').'%';
                $q->where(fn ($q) => $q->where('titulo', 'like', $termo)->orWhere('descricao', 'like', $termo));
            })
            ->orderByRaw('RAND(?)', [(int) $request->session()->get('lista_seed')])
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        return view('anuncios.index', [
            'anuncios' => $anuncios,
            'categorias' => Categoria::where('status', true)->orderBy('nome')->get(),
            'distritos' => Distrito::orderBy('nome')->get(),
        ]);
    }

    public function show(Anuncio $anuncio): View
    {
        $this->authorize('view', $anuncio);

        $anuncio->load(['categoria', 'distrito', 'concelho', 'galeria', 'user', 'reviews.user']);
        $anuncio->increment('views');

        $favorito = Auth::check()
            ? $anuncio->favoritos()->where('user_id', Auth::id())->exists()
            : false;

        return view('anuncios.show', ['anuncio' => $anuncio, 'favorito' => $favorito]);
    }

    public function create(): View
    {
        return view('anuncios.create', [
            'categorias' => Categoria::where('status', true)->orderBy('nome')->get(),
            'distritos' => Distrito::orderBy('nome')->get(),
        ]);
    }

    public function store(StoreAnuncioRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $user = Auth::user();
        $mensagemConta = null;

        if (! $user) {
            $user = User::where('email', $data['email'])->first();

            if (! $user) {
                $plainPassword = Str::random(12);

                $user = User::create([
                    'username' => $this->generateUniqueUsername($data['email']),
                    'email' => $data['email'],
                    'nome' => $data['minha_empresa'],
                    'telefone' => Str::limit($data['contacto'], 30, ''),
                    'password' => Hash::make($plainPassword),
                    'user_type' => 'cliente',
                    'status' => 'aprovado',
                    'email_verified_at' => now(),
                ]);

                // A mail problem (e.g. a mistyped address) must never stop the
                // anúncio from being published.
                try {
                    $user->notify(new AccountAutoCreatedNotification($plainPassword));
                } catch (\Throwable $e) {
                    report($e);
                }

                Auth::login($user);
                $mensagemConta = 'Criámos uma conta para ti. Verifica o teu email para veres os dados de acesso.';
            } else {
                $mensagemConta = 'Já existe uma conta com este email. Inicia sessão para gerires este anúncio.';
            }
        }

        $anuncio = Anuncio::create([
            ...collect($data)->only([
                'titulo', 'minha_empresa', 'categoria_id', 'distrito_id',
                'descricao', 'contacto', 'email', 'youtube_link', 'instagram_link',
            ])->all(),
            'user_id' => $user->id,
            'destacar' => $request->boolean('destacar'),
            'certificacao_profissional' => $request->boolean('certificacao_profissional'),
            'seguro_responsabilidade_civil' => $request->boolean('seguro_responsabilidade_civil'),
            'status' => 'pendente',
        ]);

        $status = 'pendente';

        if (Configuracao::get('auto_approval_enabled') === '1') {
            if ($this->moderacao->verificar($data['titulo'], $data['descricao'])) {
                $anuncio->aprovar();
                $this->publicadoNotifier->notificar($anuncio);
                $status = 'aprovado';
            } else {
                $anuncio->update(['status' => 'rejeitado']);
                $status = 'rejeitado';
            }
        }

        $user->update(['lembrete_anuncio_status' => 0]);

        $this->images->storeGaleria($anuncio, $request);

        $mensagens = [
            'pendente' => 'Anúncio submetido com sucesso! Fica pendente de aprovação.',
            'aprovado' => 'Anúncio submetido e aprovado automaticamente.',
            'rejeitado' => 'O anúncio foi rejeitado automaticamente por conter conteúdo não permitido.',
        ];

        // A visitor who published without being logged in (the email already has
        // an account) can't open an anúncio that is still pending - that used to
        // end in a "403 This action is unauthorized" page. Send them to the login
        // instead, and bring them to their anúncio once they are in.
        if (! Auth::check()) {
            session(['url.intended' => route('anuncios.show', $anuncio)]);

            return redirect()
                ->route('login')
                ->with('status', $mensagens[$status].' '.$mensagemConta);
        }

        return redirect()
            ->route('anuncios.show', $anuncio)
            ->with('status', $mensagemConta ?? $mensagens[$status]);
    }

    public function edit(Anuncio $anuncio): View
    {
        $this->authorize('update', $anuncio);

        return view('anuncios.edit', [
            'anuncio' => $anuncio->load('galeria'),
            'categorias' => Categoria::where('status', true)->orderBy('nome')->get(),
            'distritos' => Distrito::orderBy('nome')->get(),
        ]);
    }

    public function update(UpdateAnuncioRequest $request, Anuncio $anuncio): RedirectResponse
    {
        $data = $request->validated();

        $anuncio->update([
            ...collect($data)->only([
                'titulo', 'minha_empresa', 'categoria_id', 'distrito_id',
                'descricao', 'contacto', 'email', 'youtube_link', 'instagram_link',
            ])->all(),
            'destacar' => $request->boolean('destacar'),
            'certificacao_profissional' => $request->boolean('certificacao_profissional'),
            'seguro_responsabilidade_civil' => $request->boolean('seguro_responsabilidade_civil'),
        ]);

        $this->images->storeGaleria($anuncio, $request);

        return redirect()->route('anuncios.show', $anuncio)->with('status', 'Anúncio atualizado.');
    }

    public function reativar(Anuncio $anuncio): RedirectResponse
    {
        $this->authorize('update', $anuncio);

        abort_unless($anuncio->status === 'expirado', 400);

        $anuncio->aprovar();

        return back()->with('status', 'Anúncio reativado.');
    }

    public function destroy(Anuncio $anuncio): RedirectResponse
    {
        $this->authorize('delete', $anuncio);

        foreach ($anuncio->galeria as $imagem) {
            $this->images->delete($imagem->imagem);
        }

        $anuncio->delete();

        return redirect()->route('anuncios.meus')->with('status', 'Anúncio removido.');
    }

    public function meus(): View
    {
        $anuncios = Auth::user()->anuncios()
            ->with(['categoria', 'galeria'])
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('anuncios.meus', ['anuncios' => $anuncios]);
    }

    /**
     * The phone number is hidden on the page until the visitor clicks
     * "Ver contacto" - this endpoint logs that as a phone_clicks visit
     * (surfaced in admin > Analytics) and returns the number to reveal.
     */
    public function revelarTelefone(Anuncio $anuncio): JsonResponse
    {
        $this->authorize('view', $anuncio);

        $anuncio->increment('phone_clicks');
        \App\Models\ContactoEvento::registar('telefone', $anuncio);

        // Tell the owner someone saved their contact - after the response is
        // sent so a slow mail server never delays showing the number.
        $ip = request()->ip();
        app()->terminating(fn () => $this->contactoNotifier->notificar($anuncio, $ip));

        return response()->json(['contacto' => $anuncio->contacto]);
    }

    /**
     * Same idea as revelarTelefone() but for the WhatsApp button - logs a
     * whatsapp_clicks visit before handing back the wa.me link to open.
     */
    public function revelarWhatsapp(Anuncio $anuncio): JsonResponse
    {
        $this->authorize('view', $anuncio);

        $url = $anuncio->whatsappUrl();
        abort_unless($url, 404);

        $anuncio->increment('whatsapp_clicks');
        \App\Models\ContactoEvento::registar('whatsapp', $anuncio);

        return response()->json(['url' => $url]);
    }

    public function destroyImagem(GaleriaAnuncio $imagem): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $imagem->anuncio);

        $this->images->delete($imagem->imagem);
        $imagem->delete();

        if (request()->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return back()->with('status', 'Imagem removida.');
    }

    /**
     * Saves a new gallery order after the images were dragged around: the
     * request carries the image ids in their new sequence (first = capa).
     */
    public function ordenarImagens(Request $request): JsonResponse
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1', 'max:'.ImageUploadService::MAX_GALERIA],
            'ids.*' => ['integer'],
        ]);

        $imagens = GaleriaAnuncio::with('anuncio')->whereIn('id', $data['ids'])->get()->keyBy('id');

        abort_unless($imagens->count() === count($data['ids']), 422);
        abort_unless($imagens->pluck('anuncio_id')->unique()->count() === 1, 422);
        $this->authorize('update', $imagens->first()->anuncio);

        foreach (array_values($data['ids']) as $posicao => $id) {
            $imagens[$id]->update(['ordem' => $posicao + 1]);
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Quarter-turn rotation of one gallery image (for photos that were
     * uploaded sideways). Returns the image URL with a cache-buster so the
     * page can refresh the thumbnail without reloading.
     */
    public function rodarImagem(Request $request, GaleriaAnuncio $imagem): JsonResponse
    {
        $this->authorize('update', $imagem->anuncio);

        $request->validate(['sentido' => ['required', 'in:esquerda,direita']]);

        $this->images->rotate($imagem->imagem, $request->input('sentido') === 'direita');

        return response()->json(['url' => Storage::url($imagem->imagem).'?v='.time()]);
    }

    private function generateUniqueUsername(string $email): string
    {
        $base = Str::slug(Str::before($email, '@')) ?: 'user';
        $username = $base;
        $suffix = 1;

        while (User::where('username', $username)->exists()) {
            $username = $base.$suffix++;
        }

        return $username;
    }
}
