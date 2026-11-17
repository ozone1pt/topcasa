<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-200">Mensagens</h2>
    </x-slot>

    <div class="py-6 max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-slate-800 rounded-lg shadow divide-y dark:divide-slate-700">
            @forelse ($mensagens as $mensagem)
                <a href="{{ route('mensagens.show', $mensagem) }}" class="block p-4 hover:bg-slate-50 dark:hover:bg-slate-700 {{ ! $mensagem->lida && $mensagem->destinatario_id === auth()->id() ? 'font-semibold' : '' }}">
                    <div class="flex justify-between">
                        <span class="text-slate-900 dark:text-slate-100">{{ $mensagem->anuncio->titulo }}</span>
                        <span class="text-xs text-slate-500">{{ $mensagem->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="text-sm text-slate-600 dark:text-slate-400 truncate">
                        {{ $mensagem->remetenteNome() }}: {{ $mensagem->mensagem }}
                    </p>
                </a>
            @empty
                <p class="p-4 text-slate-500 dark:text-slate-400">Sem mensagens.</p>
            @endforelse
        </div>

        {{ $mensagens->links() }}
    </div>
</x-app-layout>
