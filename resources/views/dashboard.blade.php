<x-app-layout>
    <x-slot name="header">
        <h2 class="font-display font-semibold text-xl text-slate-800 dark:text-slate-200">
            Olá, {{ auth()->user()->nomeCompleto() }}
        </h2>
    </x-slot>

    <div class="py-8 max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            @php
                $user = auth()->user();
            @endphp
            @foreach ([
                ['label' => 'Os meus anúncios', 'value' => $user->anuncios()->count(), 'href' => route('anuncios.meus'), 'icon' => 'fa-bullhorn'],
                ['label' => 'Favoritos', 'value' => $user->favoritos()->count(), 'href' => route('favoritos.index'), 'icon' => 'fa-heart'],
                ['label' => 'Mensagens por ler', 'value' => \App\Models\Mensagem::where('destinatario_id', $user->id)->where('lida', false)->count(), 'href' => route('mensagens.index'), 'icon' => 'fa-comment'],
                ['label' => 'Alertas por ler', 'value' => $user->alertas()->where('lida', false)->count(), 'href' => route('alertas.index'), 'icon' => 'fa-bell'],
            ] as $card)
                <a href="{{ $card['href'] }}" class="card p-4 hover:shadow-md hover:border-green-300 dark:hover:border-green-600 transition">
                    <span class="w-9 h-9 flex items-center justify-center rounded-full bg-green-50 dark:bg-green-900/40 text-green-600 dark:text-green-400 mb-2">
                        <i class="fa-solid {{ $card['icon'] }}"></i>
                    </span>
                    <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ $card['value'] }}</p>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ $card['label'] }}</p>
                </a>
            @endforeach
        </div>

        <div class="card p-6 flex flex-wrap gap-3">
            <a href="{{ route('anuncios.create') }}" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-plus"></i> Publicar anúncio
            </a>
            <a href="{{ route('orcamentos.create') }}" class="btn btn-outline btn-sm">
                <i class="fa-solid fa-file-invoice"></i> Pedir orçamento
            </a>
            <a href="{{ route('profile.edit') }}" class="btn btn-outline btn-sm">
                <i class="fa-solid fa-user"></i> Editar perfil
            </a>
        </div>
    </div>
</x-app-layout>
