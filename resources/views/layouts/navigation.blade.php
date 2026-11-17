<nav x-data="{ open: false }" class="bg-white dark:bg-slate-900 border-b border-slate-100 dark:border-slate-800 sticky top-0 z-40">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center gap-8">
                <a href="{{ route('home') }}">
                    <x-logo class="h-9 w-auto" />
                </a>

                <div class="hidden lg:flex lg:items-center lg:gap-6">
                    <x-nav-link :href="route('anuncios.index')" :active="request()->routeIs('anuncios.index')">Anúncios</x-nav-link>
                    <x-nav-link :href="route('orcamentos.index')" :active="request()->routeIs('orcamentos.*')">Orçamentos</x-nav-link>
                    <x-nav-link :href="route('recrutamentos.index')" :active="request()->routeIs('recrutamentos.*')">Recrutamento</x-nav-link>
                    <x-nav-link :href="route('blog.index')" :active="request()->routeIs('blog.*')">Blog</x-nav-link>
                    @auth
                        <x-nav-link :href="route('anuncios.meus')" :active="request()->routeIs('anuncios.meus')">Os meus anúncios</x-nav-link>
                        <x-nav-link :href="route('favoritos.index')" :active="request()->routeIs('favoritos.index')">Favoritos</x-nav-link>
                        <x-nav-link :href="route('mensagens.index')" :active="request()->routeIs('mensagens.*')">Mensagens</x-nav-link>
                        <x-nav-link :href="route('alertas.index')" :active="request()->routeIs('alertas.index')">Alertas</x-nav-link>
                        @can('admin')
                            <x-nav-link :href="route('crm.dashboard')" :active="request()->routeIs('crm.*')">CRM</x-nav-link>
                        @endcan
                    @endauth
                </div>
            </div>

            <div class="hidden lg:flex lg:items-center lg:gap-3">
                @auth
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center gap-1 px-3 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white transition">
                                {{ Auth::user()->nomeCompleto() }}
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <x-dropdown-link :href="route('dashboard')">{{ __('Painel') }}</x-dropdown-link>
                            <x-dropdown-link :href="route('profile.edit')">{{ __('Perfil') }}</x-dropdown-link>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                    {{ __('Sair') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @else
                    <a href="{{ route('login') }}" class="btn btn-outline btn-sm">Entrar</a>
                @endauth
                <a href="{{ route('anuncios.create') }}" class="btn btn-primary btn-sm">Publicar anúncio</a>
            </div>

            <div class="-me-2 flex items-center lg:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-slate-400 hover:text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 focus:outline-none transition">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden lg:hidden border-t border-slate-100 dark:border-slate-800">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('anuncios.index')" :active="request()->routeIs('anuncios.index')">Anúncios</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('orcamentos.index')" :active="request()->routeIs('orcamentos.*')">Orçamentos</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('recrutamentos.index')" :active="request()->routeIs('recrutamentos.*')">Recrutamento</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('blog.index')" :active="request()->routeIs('blog.*')">Blog</x-responsive-nav-link>
            @auth
                <x-responsive-nav-link :href="route('anuncios.meus')" :active="request()->routeIs('anuncios.meus')">Os meus anúncios</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('favoritos.index')" :active="request()->routeIs('favoritos.index')">Favoritos</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('mensagens.index')" :active="request()->routeIs('mensagens.*')">Mensagens</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('alertas.index')" :active="request()->routeIs('alertas.index')">Alertas</x-responsive-nav-link>
                @can('admin')
                    <x-responsive-nav-link :href="route('crm.dashboard')" :active="request()->routeIs('crm.*')">CRM</x-responsive-nav-link>
                @endcan
            @endauth
        </div>

        <div class="pt-4 pb-4 border-t border-slate-200 dark:border-slate-700 px-4 space-y-3">
            @auth
                <div>
                    <div class="font-medium text-base text-slate-800 dark:text-slate-200">{{ Auth::user()->nomeCompleto() }}</div>
                    <div class="font-medium text-sm text-slate-500">{{ Auth::user()->email }}</div>
                </div>
                <div class="space-y-1">
                    <x-responsive-nav-link :href="route('dashboard')">{{ __('Painel') }}</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('profile.edit')">{{ __('Perfil') }}</x-responsive-nav-link>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                            {{ __('Sair') }}
                        </x-responsive-nav-link>
                    </form>
                </div>
            @else
                <a href="{{ route('login') }}" class="btn btn-outline btn-sm btn-block">Entrar</a>
            @endauth
            <a href="{{ route('anuncios.create') }}" class="btn btn-primary btn-sm btn-block">Publicar anúncio</a>
        </div>
    </div>
</nav>
