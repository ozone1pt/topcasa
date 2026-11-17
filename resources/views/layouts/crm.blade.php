<!DOCTYPE html>
<html lang="pt-PT">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="robots" content="noindex, nofollow">

        <title>{{ $title ? "{$title} · TopCasa CRM" : 'TopCasa CRM' }}</title>
        <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=montserrat:400,500,600,700,800&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-slate-100">
        <div class="min-h-screen flex" x-data="{ sidebarOpen: false }">
            <!-- Sidebar -->
            <aside
                class="fixed lg:static inset-y-0 left-0 z-40 w-64 bg-slate-900 text-slate-300 flex flex-col shrink-0 transition-transform -translate-x-full lg:translate-x-0"
                :class="sidebarOpen && '!translate-x-0'"
            >
                <div class="h-16 flex items-center gap-2 px-5 border-b border-slate-800">
                    <x-logo variant="white" class="h-7 w-auto" />
                    <span class="text-xs font-bold uppercase tracking-wider text-green-400 bg-green-500/10 rounded px-1.5 py-0.5">CRM</span>
                </div>

                <nav class="flex-1 overflow-y-auto py-4 space-y-1">
                    @php
                        $links = [
                            ['route' => 'crm.dashboard', 'icon' => 'fa-gauge', 'label' => 'Dashboard'],
                            ['route' => 'crm.users.index', 'icon' => 'fa-users', 'label' => 'Clientes'],
                            ['route' => 'crm.anuncios.index', 'icon' => 'fa-rectangle-ad', 'label' => 'Anúncios'],
                            ['route' => 'crm.orcamentos.index', 'icon' => 'fa-file-invoice-dollar', 'label' => 'Orçamentos'],
                            ['route' => 'crm.recrutamentos.index', 'icon' => 'fa-briefcase', 'label' => 'Recrutamento'],
                            ['route' => 'crm.contactos.index', 'icon' => 'fa-chart-line', 'label' => 'Contactos'],
                        ];
                        $maisLinks = [
                            ['route' => 'crm.categorias.index', 'icon' => 'fa-tags', 'label' => 'Categorias'],
                            ['route' => 'crm.banners.index', 'icon' => 'fa-image', 'label' => 'Banners'],
                            ['route' => 'crm.empresas.index', 'icon' => 'fa-building', 'label' => 'Empresas apoiadoras'],
                            ['route' => 'crm.blog.index', 'icon' => 'fa-newspaper', 'label' => 'Blog'],
                            ['route' => 'crm.newsletter.index', 'icon' => 'fa-envelope', 'label' => 'Newsletter'],
                            ['route' => 'crm.alertas.index', 'icon' => 'fa-bell', 'label' => 'Alertas'],
                            ['route' => 'crm.analytics.index', 'icon' => 'fa-magnifying-glass-chart', 'label' => 'Analytics'],
                            ['route' => 'crm.configuracoes.edit', 'icon' => 'fa-gear', 'label' => 'Definições'],
                        ];
                    @endphp

                    @foreach ($links as $link)
                        <a
                            href="{{ route($link['route']) }}"
                            @class([
                                'flex items-center gap-3 px-5 py-2.5 text-sm font-medium transition',
                                'bg-green-500/10 text-green-400 border-r-2 border-green-500' => request()->routeIs($link['route'].'*'),
                                'hover:bg-slate-800 hover:text-white' => ! request()->routeIs($link['route'].'*'),
                            ])
                        >
                            <i class="fa-solid {{ $link['icon'] }} w-4 text-center"></i> {{ $link['label'] }}
                        </a>
                    @endforeach

                    <p class="px-5 pt-4 pb-1 text-[11px] font-semibold uppercase tracking-wider text-slate-500">Mais</p>
                    @foreach ($maisLinks as $link)
                        <a
                            href="{{ route($link['route']) }}"
                            @class([
                                'flex items-center gap-3 px-5 py-2 text-sm transition',
                                'bg-green-500/10 text-green-400' => request()->routeIs($link['route'].'*'),
                                'text-slate-400 hover:bg-slate-800 hover:text-white' => ! request()->routeIs($link['route'].'*'),
                            ])
                        >
                            <i class="fa-solid {{ $link['icon'] }} w-4 text-center"></i> {{ $link['label'] }}
                        </a>
                    @endforeach
                </nav>

                <div class="border-t border-slate-800 p-4 space-y-1">
                    <a href="{{ route('home') }}" class="flex items-center gap-2 text-sm text-slate-400 hover:text-white transition">
                        <i class="fa-solid fa-arrow-up-right-from-square w-4 text-center"></i> Ver site
                    </a>
                </div>
            </aside>

            <div class="fixed inset-0 bg-black/40 z-30 lg:hidden" x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"></div>

            <!-- Main -->
            <div class="flex-1 min-w-0 flex flex-col">
                <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 sm:px-6 shrink-0">
                    <div class="flex items-center gap-3">
                        <button type="button" class="lg:hidden text-slate-500" @click="sidebarOpen = ! sidebarOpen">
                            <i class="fa-solid fa-bars text-lg"></i>
                        </button>
                        <h1 class="hidden sm:block font-display font-bold text-lg text-slate-900 whitespace-nowrap">{{ $title }}</h1>
                    </div>

                    <form method="GET" action="{{ route('crm.pesquisa') }}" class="flex-1 min-w-0 mx-3 sm:mx-6 max-w-xl" role="search">
                        <label for="crm-pesquisa" class="sr-only">Pesquisar</label>
                        <div class="relative">
                            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                            <input
                                id="crm-pesquisa"
                                type="search"
                                name="q"
                                value="{{ request()->routeIs('crm.pesquisa') ? request('q') : '' }}"
                                placeholder="Pesquisar anúncios, clientes, orçamentos..."
                                minlength="2"
                                class="w-full pl-9 pr-3 py-2 text-sm rounded-lg border-slate-300 bg-slate-50 focus:bg-white focus:border-green-500 focus:ring-green-500"
                            />
                        </div>
                    </form>

                    <div class="relative" x-data="{ open: false }" @click.away="open = false">
                        <button type="button" @click="open = ! open" class="flex items-center gap-2 text-sm font-medium text-slate-700 hover:text-slate-900">
                            <span class="icon-circle w-8 h-8 text-xs">{{ strtoupper(substr(auth()->user()->nomeCompleto() ?: auth()->user()->username, 0, 1)) }}</span>
                            <span class="hidden sm:inline">{{ auth()->user()->nomeCompleto() ?: auth()->user()->username }}</span>
                            <i class="fa-solid fa-chevron-down text-xs text-slate-400"></i>
                        </button>
                        <div x-show="open" x-cloak class="absolute right-0 z-20 mt-2 w-48 bg-white rounded-lg shadow-lg border border-slate-200 py-1 text-left">
                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                <i class="fa-solid fa-key w-4"></i> Alterar password
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                    <i class="fa-solid fa-arrow-right-from-bracket w-4"></i> Sair
                                </button>
                            </form>
                        </div>
                    </div>
                </header>

                <main class="flex-1 p-4 sm:p-6 max-w-7xl w-full mx-auto">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
