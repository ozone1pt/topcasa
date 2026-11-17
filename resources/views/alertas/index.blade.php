<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-200">Os meus alertas</h2>
    </x-slot>

    <div class="py-6 max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-3">
        @forelse ($alertas as $alerta)
            <div class="bg-white dark:bg-slate-800 rounded-lg shadow p-4 {{ $alerta->lida ? 'opacity-60' : '' }}">
                <div class="flex justify-between items-start gap-3">
                    <div>
                        <p class="font-medium text-slate-900 dark:text-slate-100">{{ $alerta->titulo }}</p>
                        <p class="text-sm text-slate-600 dark:text-slate-400">{{ $alerta->mensagem }}</p>
                        <p class="text-xs text-slate-500 mt-1">{{ $alerta->created_at->diffForHumans() }}</p>
                    </div>
                    @unless ($alerta->lida)
                        <form method="POST" action="{{ route('alertas.lido', $alerta) }}">
                            @csrf
                            <button type="submit" class="text-xs underline">Marcar como lido</button>
                        </form>
                    @endunless
                </div>
            </div>
        @empty
            <p class="text-slate-500 dark:text-slate-400">Sem alertas.</p>
        @endforelse

        {{ $alertas->links() }}
    </div>
</x-app-layout>
