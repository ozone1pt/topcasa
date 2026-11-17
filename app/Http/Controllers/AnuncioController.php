<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAnuncioRequest;
use App\Http\Requests\UpdateAnuncioRequest;
use App\Models\Anuncio;
use App\Models\Categoria;
use App\Models\Distrito;
use App\Models\GaleriaAnuncio;
use App\Models\User;
use App\Notifications\AccountAutoCreatedNotification;
use App\Services\AnuncioListagem;
use App\Services\GestaoGaleria;
use App\Services\PublicacaoAnuncio;
use App\Services\RevelacaoContacto;
use App\Services\UsernameGenerator;
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
        private readonly AnuncioListagem $listagem,
        private readonly UsernameGenerator $usernames,
        private readonly PublicacaoAnuncio $publicacao,
        private readonly GestaoGaleria $galeria,
        private readonly RevelacaoContacto $revelacao,
    ) {}

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

        $anuncios = $this->listagem->publicados(
            categoriaId: $request->filled('categoria_id') ? $request->integer('categoria_id') : null,
            distritoId: $request->filled('distrito_id') ? $request->integer('distrito_id') : null,
            concelhoId: $request->filled('concelho_id') ? $request->integer('concelho_id') : null,
            termo: $request->filled('busca') ? (string) $request->string('busca') : null,
            seed: (int) $request->session()->get('lista_seed'),
        )->paginate(AnuncioListagem::POR_PAGINA)->withQueryString();

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
                    'username' => $this->usernames->fromSeed($data['email']),
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

        $anuncio = $this->publicacao->criar($user, $data, $request);
        $status = $anuncio->status;

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
        $this->publicacao->atualizar($anuncio, $request->validated(), $request);

        return redirect()->route('anuncios.show', $anuncio)->with('status', 'Anúncio atualizado.');
    }

    public function reativar(Anuncio $anuncio): RedirectResponse
    {
        $this->authorize('update', $anuncio);

        $this->publicacao->reativar($anuncio);

        return back()->with('status', 'Anúncio reativado.');
    }

    public function destroy(Anuncio $anuncio): RedirectResponse
    {
        $this->authorize('delete', $anuncio);

        $this->publicacao->apagar($anuncio);

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
    public function revelarTelefone(Request $request, Anuncio $anuncio): JsonResponse
    {
        $this->authorize('view', $anuncio);

        return response()->json([
            'contacto' => $this->revelacao->telefone($anuncio, (string) $request->ip()),
        ]);
    }

    /**
     * Same idea as revelarTelefone() but for the WhatsApp button - logs a
     * whatsapp_clicks visit before handing back the wa.me link to open.
     */
    public function revelarWhatsapp(Anuncio $anuncio): JsonResponse
    {
        $this->authorize('view', $anuncio);

        return response()->json(['url' => $this->revelacao->whatsapp($anuncio)]);
    }

    public function destroyImagem(GaleriaAnuncio $imagem): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $imagem->anuncio);

        $this->galeria->remover($imagem);

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
        $ids = $this->galeria->idsValidados($request);
        $imagens = GaleriaAnuncio::with('anuncio')->whereIn('id', $ids)->get();

        abort_unless($imagens->count() === count($ids), 422);
        abort_unless($imagens->pluck('anuncio_id')->unique()->count() === 1, 422);
        $this->authorize('update', $imagens->first()->anuncio);

        $this->galeria->ordenar($ids);

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

        $this->galeria->rodar($imagem, $request->input('sentido') === 'direita');

        return response()->json(['url' => Storage::url($imagem->imagem).'?v='.time()]);
    }
}
