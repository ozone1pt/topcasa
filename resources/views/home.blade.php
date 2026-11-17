<x-app-layout description="A TopCasa liga pessoas a profissionais de confiança para resolver tudo o que precisam em casa, de forma simples, rápida e segura.">
    <!-- Hero -->
    <section class="bg-slate-50 dark:bg-slate-950 border-b border-slate-200 dark:border-slate-800">
        <div class="w-full relative">
            <x-image-carousel
                :images="[
                    ['src' => asset('images/banners-home/rollup-1-publique-gratis.jpg').'?v=1', 'alt' => 'TopCasa - Mais clientes para o seu negócio. Publique grátis!'],
                    ['src' => asset('images/banners-home/rollup-2.jpg').'?v=8', 'alt' => 'Encontre o profissional certo para a sua casa. Compare opções e contacte diretamente.'],
                ]"
                aspect-class="aspect-[1280/720]"
                object-position="center"
                frame-class="sm:max-h-[calc(100svh-6rem)] sm:min-h-[420px]"
            />

            <form method="GET" action="{{ route('anuncios.index') }}" class="absolute left-[9.5%] bottom-[1%] w-[81%] sm:left-[2.5%] sm:w-[44%] sm:min-w-[360px] z-10 flex flex-row gap-1 sm:gap-1.5 bg-white dark:bg-slate-800 p-1 sm:p-1.5 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700">
                <div
                    class="flex-1 min-w-0 relative flex items-center gap-1 sm:gap-2 px-2 sm:px-3"
                    x-data="{ open: false, id: '', nome: 'Categoria' }"
                    @click.away="open = false"
                >
                    <input type="hidden" name="categoria_id" :value="id" />
                    <button
                        type="button"
                        @click="open = ! open"
                        class="flex-1 min-w-0 flex items-center gap-1 sm:gap-2 py-1.5 cursor-pointer text-left overflow-hidden"
                    >
                        <i class="fa-solid fa-magnifying-glass text-slate-400 shrink-0"></i>
                        <span class="flex-1 min-w-0 truncate text-sm dark:text-slate-100" x-text="nome"></span>
                        <i class="fa-solid fa-chevron-down text-xs text-slate-400 transition shrink-0" :class="open ? 'rotate-180' : ''"></i>
                    </button>

                    <div
                        x-show="open"
                        x-cloak
                        class="absolute z-20 top-full left-0 mt-2 w-64 max-h-72 overflow-y-auto bg-white dark:bg-slate-800 rounded-lg shadow-lg border border-slate-200 dark:border-slate-700 text-left"
                    >
                        <button type="button" @click="id = ''; nome = 'Categoria'; open = false" class="block w-full text-left px-4 py-2 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700">Todas as categorias</button>
                        @foreach ($categoriasTodas as $categoria)
                            <button type="button" @click="id = '{{ $categoria->id }}'; nome = '{{ addslashes($categoria->nome) }}'; open = false" class="flex items-center gap-2 w-full text-left px-4 py-2 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700">
                                <i class="fa-solid {{ $categoria->icone ?: 'fa-house' }} text-green-500 w-4"></i>
                                {{ $categoria->nome }}
                            </button>
                        @endforeach
                    </div>
                </div>
                <div
                    class="relative flex items-center gap-1 sm:gap-2 px-1 sm:px-3 border-l border-slate-200 dark:border-slate-700 shrink-0"
                    x-data="{ open: false, id: '', nome: 'Distrito' }"
                    @click.away="open = false"
                >
                    <input type="hidden" name="distrito_id" :value="id" />
                    <button
                        type="button"
                        @click="open = ! open"
                        class="flex items-center gap-1 py-1.5 cursor-pointer text-left"
                    >
                        <span class="w-20 sm:w-24 truncate text-sm dark:text-slate-100" x-text="nome"></span>
                        <i class="fa-solid fa-chevron-down text-xs text-slate-400 transition shrink-0" :class="open ? 'rotate-180' : ''"></i>
                    </button>

                    <div
                        x-show="open"
                        x-cloak
                        class="absolute z-20 top-full right-0 mt-2 w-48 max-h-72 overflow-y-auto bg-white dark:bg-slate-800 rounded-lg shadow-lg border border-slate-200 dark:border-slate-700 text-left"
                    >
                        <button type="button" @click="id = ''; nome = 'Distrito'; open = false" class="block w-full text-left px-4 py-2 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700">Todos os distritos</button>
                        @foreach ($distritos as $distrito)
                            <button type="button" @click="id = '{{ $distrito->id }}'; nome = '{{ addslashes($distrito->nome) }}'; open = false" class="block w-full text-left px-4 py-2 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700">{{ $distrito->nome }}</button>
                        @endforeach
                    </div>
                </div>
                <button type="submit" aria-label="Pesquisar" class="btn btn-primary btn-sm shrink-0 !px-3">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
            </form>
        </div>

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pt-2 sm:pt-14 pb-10 sm:pb-14 text-center">
            <!-- Acesso rápido -->
            <div class="mt-4 sm:mt-8 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 text-left">
                <a href="{{ route('anuncios.create') }}" class="card bg-green-100 dark:bg-green-900/30 border-green-300 dark:border-green-700 p-5 flex items-center gap-4 hover:shadow-md hover:border-green-400 dark:hover:border-green-600 transition">
                    <span class="icon-circle shrink-0"><i class="fa-solid fa-circle-plus"></i></span>
                    <span>
                        <span class="block font-semibold text-slate-900 dark:text-white">Publicar Serviço Grátis</span>
                        <span class="block text-sm text-slate-500 dark:text-slate-400">Anuncie os seus serviços</span>
                    </span>
                </a>
                <a href="{{ route('dicas-seguranca') }}" class="card p-5 flex items-center gap-4 hover:shadow-md hover:border-green-300 dark:hover:border-green-600 transition">
                    <span class="icon-circle shrink-0"><i class="fa-solid fa-shield-halved"></i></span>
                    <span>
                        <span class="block font-semibold text-slate-900 dark:text-white">Dicas de Segurança</span>
                        <span class="block text-sm text-slate-500 dark:text-slate-400">Contrate com mais confiança</span>
                    </span>
                </a>
                <a href="{{ route('orcamentos.index') }}" class="card p-5 flex items-center gap-4 hover:shadow-md hover:border-green-300 dark:hover:border-green-600 transition">
                    <span class="icon-circle shrink-0"><i class="fa-solid fa-file-lines"></i></span>
                    <span>
                        <span class="block font-semibold text-slate-900 dark:text-white">Ver Orçamentos</span>
                        <span class="block text-sm text-slate-500 dark:text-slate-400">Encontre clientes à procura</span>
                    </span>
                </a>
                <a href="{{ route('orcamentos.create') }}" class="card p-5 flex items-center gap-4 hover:shadow-md hover:border-green-300 dark:hover:border-green-600 transition">
                    <span class="icon-circle shrink-0"><i class="fa-solid fa-pen-to-square"></i></span>
                    <span>
                        <span class="block font-semibold text-slate-900 dark:text-white">Pedir Orçamento</span>
                        <span class="block text-sm text-slate-500 dark:text-slate-400">Publique o que precisa</span>
                    </span>
                </a>
                <a href="{{ route('recrutamentos.create') }}" class="card p-5 flex items-center gap-4 hover:shadow-md hover:border-green-300 dark:hover:border-green-600 transition">
                    <span class="icon-circle shrink-0"><i class="fa-solid fa-user-plus"></i></span>
                    <span>
                        <span class="block font-semibold text-slate-900 dark:text-white">Criar Recrutamento</span>
                        <span class="block text-sm text-slate-500 dark:text-slate-400">Publique a sua vaga</span>
                    </span>
                </a>
                <a href="{{ route('recrutamentos.index') }}" class="card p-5 flex items-center gap-4 hover:shadow-md hover:border-green-300 dark:hover:border-green-600 transition">
                    <span class="icon-circle shrink-0"><i class="fa-solid fa-briefcase"></i></span>
                    <span>
                        <span class="block font-semibold text-slate-900 dark:text-white">Ver Recrutamentos</span>
                        <span class="block text-sm text-slate-500 dark:text-slate-400">Encontre oportunidades</span>
                    </span>
                </a>
            </div>

            <p class="text-xs text-slate-400 dark:text-slate-500 mt-10">Dados atuais da plataforma · topcasa.pt</p>
            <div class="mt-3 flex flex-wrap justify-center gap-x-10 gap-y-3">
                <div><span class="font-display font-extrabold text-2xl text-slate-900 dark:text-white">{{ number_format($stats['anuncios'], 0, ',', '.') }}</span> <span class="text-sm text-slate-500 dark:text-slate-400">Total de Anúncios</span></div>
                <div><span class="font-display font-extrabold text-2xl text-slate-900 dark:text-white">{{ number_format($stats['visualizacoes'], 0, ',', '.') }}</span> <span class="text-sm text-slate-500 dark:text-slate-400">Total de Visitantes</span></div>
                <div><span class="font-display font-extrabold text-2xl text-slate-900 dark:text-white">{{ number_format($stats['contactos'], 0, ',', '.') }}</span> <span class="text-sm text-slate-500 dark:text-slate-400">Contactos Efetuados</span></div>
            </div>
        </div>
    </section>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <x-banner-slot localizacao="topo" />
    </div>

    <!-- Categorias -->
    <section class="py-16 bg-white dark:bg-slate-900 border-y border-slate-200 dark:border-slate-800" x-data="{ verTodas: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-2xl mx-auto text-center mb-10">
                <span class="section-eyebrow">Áreas mais procuradas</span>
                <h2 class="font-display font-bold text-3xl text-slate-900 dark:text-white mt-3">Encontre profissionais nas principais áreas</h2>
                <p class="text-slate-500 dark:text-slate-400 mt-2">Da canalização à jardinagem, tudo o que a sua casa precisa está a um pedido de distância.</p>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
                @foreach ($categoriasTodas as $categoria)
                    <a
                        href="{{ route('anuncios.index', ['categoria_id' => $categoria->id]) }}"
                        class="flex flex-col items-center gap-2 p-4 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-green-400 dark:hover:border-green-500 hover:shadow-md transition text-center"
                        @if ($loop->index >= 12) x-show="verTodas" x-cloak @endif
                    >
                        <span class="icon-circle"><i class="fa-solid {{ $categoria->icone ?: 'fa-house' }}"></i></span>
                        <span class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ $categoria->nome }}</span>
                    </a>
                @endforeach
            </div>

            @if ($categoriasTodas->count() > 12)
                <div class="text-center mt-8">
                    <button type="button" @click="verTodas = ! verTodas" class="btn btn-outline">
                        <span x-text="verTodas ? 'Ver menos' : 'Ver todas'"></span>
                        <i class="fa-solid fa-chevron-down text-xs transition" :class="verTodas ? 'rotate-180' : ''"></i>
                    </button>
                </div>
            @endif
        </div>
    </section>

    @if ($recentes->isNotEmpty())
        <section class="py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="font-display font-bold text-2xl text-slate-900 dark:text-white">Anúncios recentes</h2>
                    <a href="{{ route('anuncios.index') }}" class="text-sm text-green-600 dark:text-green-400 font-semibold hover:underline">Ver todos &rarr;</a>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                    @foreach ($recentes as $anuncio)
                        @include('anuncios._card', ['anuncio' => $anuncio])
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <!-- Confiança -->
    <section class="py-16 bg-slate-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-2xl mx-auto text-center mb-10">
                <span class="inline-block text-xs font-bold uppercase tracking-wider text-green-400 bg-green-500/15 rounded-full px-3 py-1">Como trabalhamos</span>
                <h2 class="font-display font-bold text-3xl text-white mt-3">Segurança e confiança em primeiro lugar</h2>
            </div>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 text-center">
                @foreach ([
                    ['icon' => 'fa-comment', 'title' => 'Contacto direto'],
                    ['icon' => 'fa-thumbs-up', 'title' => 'Avaliações reais'],
                    ['icon' => 'fa-clock', 'title' => 'Resposta rápida'],
                    ['icon' => 'fa-lock', 'title' => 'Seguro e confiável'],
                ] as $item)
                    <div>
                        <span class="icon-circle mx-auto"><i class="fa-solid {{ $item['icon'] }}"></i></span>
                        <h4 class="text-white font-semibold text-sm mt-3">{{ $item['title'] }}</h4>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    @if ($destaques->isNotEmpty())
        <section class="py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="font-display font-bold text-2xl text-slate-900 dark:text-white mb-6">Em destaque</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach ($destaques as $anuncio)
                        @include('anuncios._card', ['anuncio' => $anuncio])
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($posts->isNotEmpty())
        <section class="py-16 bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="font-display font-bold text-2xl text-slate-900 dark:text-white">Do blog</h2>
                    <a href="{{ route('blog.index') }}" class="text-sm text-green-600 dark:text-green-400 font-semibold hover:underline">Ver blog &rarr;</a>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                    @foreach ($posts as $post)
                        <a href="{{ route('blog.show', $post) }}" class="card overflow-hidden hover:shadow-md transition">
                            @if ($post->imagem_destaque)
                                <img src="{{ Storage::url($post->imagem_destaque) }}" class="w-full aspect-video object-cover" />
                            @endif
                            <div class="p-4">
                                <h3 class="font-medium text-slate-900 dark:text-slate-100">{{ $post->titulo }}</h3>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <!-- CTA -->
    <section class="py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="card bg-slate-900 border-0 p-10 sm:p-12 flex flex-col sm:flex-row items-center justify-between gap-6">
                <div class="text-center sm:text-left">
                    <h3 class="font-display font-bold text-2xl text-white">Aumente o seu negócio: receba pedidos de orçamento todos os dias.</h3>
                    <p class="text-slate-400 mt-2">Destaque-se: mostre o seu trabalho e as avaliações dos seus clientes.</p>
                </div>
                <div class="flex gap-3 shrink-0">
                    <a href="{{ route('anuncios.index') }}" class="btn btn-white">Ver anúncios</a>
                    <a href="{{ route('anuncios.create') }}" class="btn btn-primary">Publicar grátis</a>
                </div>
            </div>
        </div>
    </section>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <x-supporters-row />
    </div>
</x-app-layout>
