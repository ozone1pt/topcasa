<x-app-layout :title="$anuncio->titulo" :description="\Illuminate\Support\Str::limit(strip_tags($anuncio->descricao), 155)">
    <x-slot name="header">
        <h2 class="font-display font-semibold text-xl text-slate-800 dark:text-slate-200">{{ $anuncio->titulo }}</h2>
    </x-slot>

    @if ($anuncio->isAprovado())
        <script type="application/ld+json">
            {!! json_encode([
                '@context' => 'https://schema.org',
                '@type' => 'Service',
                'name' => $anuncio->titulo,
                'description' => strip_tags($anuncio->descricao),
                'category' => $anuncio->categoria?->nome,
                'areaServed' => $anuncio->concelho?->nome ?? $anuncio->distrito?->nome,
                'provider' => ['@type' => 'Person', 'name' => $anuncio->nomeProfissional()],
                'aggregateRating' => $anuncio->reviews->where('status', 'aprovado')->isNotEmpty() ? [
                    '@type' => 'AggregateRating',
                    'ratingValue' => round($anuncio->reviews->where('status', 'aprovado')->avg('rating'), 1),
                    'reviewCount' => $anuncio->reviews->where('status', 'aprovado')->count(),
                ] : null,
            ], JSON_UNESCAPED_UNICODE) !!}
        </script>
    @endif

    <div class="py-6 max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
        @if (session('status'))
            <div class="bg-green-100 text-green-800 rounded-md p-3 text-sm">{{ session('status') }}</div>
        @endif

        @if (! $anuncio->isAprovado())
            <div class="bg-amber-100 text-amber-800 rounded-md p-3 text-sm">
                Este anúncio está com estado: <strong>{{ $anuncio->status }}</strong> (só tu e a administração o veem assim).
            </div>
        @endif

        <div class="bg-white dark:bg-slate-800 rounded-lg shadow p-6 space-y-4">
            @if ($anuncio->galeria->isNotEmpty())
                <div
                    class="-mx-6 sm:mx-0 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-1 sm:gap-2"
                    x-data="{
                        lightbox: false,
                        index: 0,
                        images: {{ Illuminate\Support\Js::from($anuncio->galeria->map(fn ($i) => Storage::url($i->imagem))->values()) }},
                        abrir(i) { this.index = i; this.lightbox = true; },
                        seguinte() { this.index = (this.index + 1) % this.images.length; },
                        anterior() { this.index = (this.index - 1 + this.images.length) % this.images.length; },
                        touchX: null,
                    }"
                >
                    @foreach ($anuncio->galeria as $i => $imagem)
                        <img
                            src="{{ Storage::url($imagem->imagem) }}"
                            class="sm:rounded-md aspect-video object-cover w-full cursor-pointer hover:opacity-90 transition"
                            @click="abrir({{ $i }})"
                        />
                    @endforeach

                    <div
                        x-show="lightbox"
                        x-cloak
                        class="fixed inset-0 z-50 bg-black/90 flex items-center justify-center"
                        @click.self="lightbox = false"
                        @keydown.window.escape="lightbox = false"
                        @keydown.window.arrow-right="lightbox && seguinte()"
                        @keydown.window.arrow-left="lightbox && anterior()"
                        @touchstart="touchX = $event.changedTouches[0].clientX"
                        @touchend="
                            let dx = $event.changedTouches[0].clientX - touchX;
                            if (dx > 40) anterior(); else if (dx < -40) seguinte();
                        "
                    >
                        <button type="button" @click="lightbox = false" aria-label="Fechar" class="absolute top-4 right-4 text-white text-3xl w-10 h-10 flex items-center justify-center hover:opacity-75">
                            <i class="fa-solid fa-xmark"></i>
                        </button>

                        <button
                            type="button"
                            x-show="images.length > 1"
                            @click.stop="anterior()"
                            aria-label="Anterior"
                            class="absolute left-2 sm:left-4 top-1/2 -translate-y-1/2 w-11 h-11 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center"
                        >
                            <i class="fa-solid fa-chevron-left"></i>
                        </button>

                        <img :src="images[index]" class="max-h-[85vh] max-w-[90vw] object-contain rounded-md" @click.stop />

                        <button
                            type="button"
                            x-show="images.length > 1"
                            @click.stop="seguinte()"
                            aria-label="Seguinte"
                            class="absolute right-2 sm:right-4 top-1/2 -translate-y-1/2 w-11 h-11 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center"
                        >
                            <i class="fa-solid fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            @endif

            <div class="flex flex-wrap gap-2 text-sm">
                <span class="bg-slate-100 dark:bg-slate-700 rounded px-2 py-1">{{ $anuncio->categoria?->nome }}</span>
                <span class="bg-slate-100 dark:bg-slate-700 rounded px-2 py-1">{{ collect([$anuncio->concelho?->nome, $anuncio->distrito?->nome])->filter()->implode(', ') }}</span>
                @if ($anuncio->certificacao_profissional)
                    <span class="bg-blue-100 text-blue-800 rounded px-2 py-1">Certificação profissional</span>
                @endif
                @if ($anuncio->seguro_responsabilidade_civil)
                    <span class="bg-blue-100 text-blue-800 rounded px-2 py-1">Seguro de responsabilidade civil</span>
                @endif
            </div>

            <p class="text-slate-700 dark:text-slate-300 whitespace-pre-line">{{ $anuncio->descricao }}</p>

            @if ($anuncio->youtubeEmbedUrl())
                <div class="aspect-video">
                    <iframe src="{{ $anuncio->youtubeEmbedUrl() }}" class="w-full h-full rounded-md" allowfullscreen></iframe>
                </div>
            @endif

            <div
                class="border-t dark:border-slate-700 pt-4 flex flex-wrap items-center gap-3"
                x-data="{
                    telefone: null,
                    aCarregar: false,
                    erro: '',
                    async pedir(url) {
                        const res = await fetch(url, {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        });
                        if (! res.ok) { throw new Error('HTTP ' + res.status); }
                        return await res.json();
                    },
                    telHref() {
                        const m = (this.telefone || '').match(/\+?\d[\d\s]{7,}\d/);
                        return 'tel:' + (m ? m[0].replace(/\s/g, '') : (this.telefone || '').replace(/[^+\d]/g, ''));
                    },
                    async verContacto() {
                        if (this.aCarregar) { return; }
                        this.aCarregar = true;
                        this.erro = '';
                        try {
                            const data = await this.pedir('{{ route('anuncios.revelar-telefone', $anuncio) }}');
                            this.telefone = data.contacto;
                        } catch (e) {
                            this.erro = 'Não foi possível mostrar o contacto. Atualiza a página e tenta de novo.';
                        } finally {
                            this.aCarregar = false;
                        }
                    },
                    async abrirWhatsapp() {
                        if (this.aCarregar) { return; }
                        this.aCarregar = true;
                        this.erro = '';
                        try {
                            const data = await this.pedir('{{ route('anuncios.revelar-whatsapp', $anuncio) }}');
                            if (data.url) {
                                // A popup opened after the request is blocked on phones and in
                                // in-app browsers, so fall back to opening WhatsApp in this tab.
                                const win = window.open(data.url, '_blank');
                                if (! win) { window.location.href = data.url; }
                            }
                        } catch (e) {
                            this.erro = 'Não foi possível abrir o WhatsApp. Atualiza a página e tenta de novo.';
                        } finally {
                            this.aCarregar = false;
                        }
                    },
                }"
            >
                <span class="text-slate-900 dark:text-slate-100 font-medium">{{ $anuncio->nomeProfissional() }}</span>

                <span x-show="! telefone" class="text-slate-600 dark:text-slate-400">
                    <button type="button" @click="verContacto()" :disabled="aCarregar" class="btn btn-outline btn-sm">
                        <i class="fa-solid fa-phone"></i> Ver contacto
                    </button>
                </span>
                <a x-cloak x-show="telefone" :href="telHref()" x-text="telefone" class="text-slate-700 dark:text-slate-200 font-semibold underline"></a>

                @if ($anuncio->whatsappUrl())
                    <button type="button" @click="abrirWhatsapp()" :disabled="aCarregar" class="btn btn-primary btn-sm">
                        <i class="fa-brands fa-whatsapp"></i> WhatsApp
                    </button>
                @endif

                <p x-cloak x-show="erro" x-text="erro" class="w-full text-sm text-red-600"></p>

                @if ($anuncio->instagramUrl())
                    <a href="{{ $anuncio->instagramUrl() }}" target="_blank" rel="noopener" class="btn btn-outline btn-sm">
                        <i class="fa-brands fa-instagram"></i> Instagram
                    </a>
                @endif

                @auth
                    <form method="POST" action="{{ route('favoritos.toggle', $anuncio) }}" class="ms-auto">
                        @csrf
                        <button type="submit" class="text-sm underline">
                            {{ $favorito ? 'Remover dos favoritos' : 'Adicionar aos favoritos' }}
                        </button>
                    </form>
                @endauth
            </div>

            @can('update', $anuncio)
                <div class="border-t dark:border-slate-700 pt-4 flex gap-3">
                    <a href="{{ route('anuncios.edit', $anuncio) }}" class="text-sm underline">Editar</a>
                    <form method="POST" action="{{ route('anuncios.destroy', $anuncio) }}" onsubmit="return confirm('Apagar este anúncio?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-sm text-red-600 underline">Apagar</button>
                    </form>
                </div>
            @endcan
        </div>

        @if ($anuncio->isAprovado())
            <div class="bg-white dark:bg-slate-800 rounded-lg shadow p-6 space-y-4">
                <h3 class="font-semibold text-slate-900 dark:text-slate-100">Contactar</h3>
                <form method="POST" action="{{ route('mensagens.store', $anuncio) }}" class="space-y-3">
                    @csrf
                    @guest
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <x-text-input name="remetente_nome" placeholder="O teu nome" class="w-full" required />
                            <x-text-input name="remetente_email" type="email" placeholder="O teu email" class="w-full" required />
                        </div>
                    @endguest
                    <textarea name="mensagem" rows="3" class="block w-full border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 rounded-md" placeholder="A tua mensagem" required></textarea>
                    <x-primary-button>Enviar mensagem</x-primary-button>
                </form>
            </div>

            <div class="bg-white dark:bg-slate-800 rounded-lg shadow p-6 space-y-4">
                <h3 class="font-semibold text-slate-900 dark:text-slate-100">
                    Avaliações
                    @if ($anuncio->reviews->where('status', 'aprovado')->isNotEmpty())
                        <span class="text-sm text-slate-500 font-normal">({{ number_format($anuncio->reviews->where('status', 'aprovado')->avg('rating'), 1) }}/5)</span>
                    @endif
                </h3>

                @foreach ($anuncio->reviews->where('status', 'aprovado') as $review)
                    <div class="border-t dark:border-slate-700 pt-3">
                        <div class="text-amber-500">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</div>
                        <p class="text-sm text-slate-700 dark:text-slate-300">{{ $review->comment }}</p>
                        <p class="text-xs text-slate-500">{{ $review->user->nomeCompleto() }}</p>
                    </div>
                @endforeach

                @auth
                    @if (auth()->id() !== $anuncio->user_id && ! $anuncio->reviews->where('user_id', auth()->id())->count())
                        <form method="POST" action="{{ route('reviews.store', $anuncio) }}" class="border-t dark:border-slate-700 pt-4 space-y-2">
                            @csrf
                            <select name="rating" class="border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 rounded-md" required>
                                <option value="">Classificação</option>
                                @for ($i = 5; $i >= 1; $i--)
                                    <option value="{{ $i }}">{{ $i }} estrelas</option>
                                @endfor
                            </select>
                            <textarea name="comment" rows="2" class="block w-full border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 rounded-md" placeholder="Comentário (opcional)"></textarea>
                            <x-primary-button>Avaliar</x-primary-button>
                        </form>
                    @endif
                @endauth
            </div>
        @endif
    </div>
</x-app-layout>
