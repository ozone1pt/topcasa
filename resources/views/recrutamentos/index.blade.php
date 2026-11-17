<x-app-layout title="Vagas de recrutamento" description="Encontra vagas de recrutamento na área de serviços domésticos em Portugal.">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-200">Vagas de recrutamento</h2>
    </x-slot>

    <div class="py-6 max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-4">
        @if (session('status'))
            <div class="bg-green-100 text-green-800 rounded-md p-3 text-sm">{{ session('status') }}</div>
        @endif

        <a href="{{ route('recrutamentos.create') }}" class="btn btn-primary btn-sm">Publicar vaga</a>

        <div class="bg-white dark:bg-slate-800 rounded-lg shadow divide-y dark:divide-slate-700">
            @forelse ($pedidos as $pedido)
                <div class="p-4">
                    <div class="flex justify-between">
                        <h3 class="font-medium text-slate-900 dark:text-slate-100">{{ $pedido->titulo }}</h3>
                        <span class="text-xs text-slate-500 text-right">{{ $pedido->distrito?->nome }}</span>
                    </div>
                    <p class="text-xs text-slate-400 mt-0.5"><i class="fa-regular fa-calendar"></i> Publicado em {{ $pedido->created_at?->timezone('Europe/Lisbon')->format('d/m/Y') }} às {{ $pedido->created_at?->timezone('Europe/Lisbon')->format('H:i') }}</p>
                    <p class="text-sm text-slate-600 dark:text-slate-400">{{ $pedido->empresa_nome }} · {{ Str::limit($pedido->descricao, 150) }}</p>
                    <p class="text-sm text-slate-500 mt-1">{{ $pedido->telefone }} · {{ $pedido->email }}</p>

                    @if (auth()->id() === $pedido->user_id || auth()->user()?->isAdmin())
                        <div class="flex gap-3 mt-2">
                            <form method="POST" action="{{ route('recrutamentos.pausar', $pedido) }}">
                                @csrf
                                <button type="submit" class="text-sm underline">{{ $pedido->status === 'ativo' ? 'Pausar' : 'Reativar' }}</button>
                            </form>
                            <form method="POST" action="{{ route('recrutamentos.destroy', $pedido) }}" onsubmit="return confirm('Remover esta vaga?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-sm text-red-600 underline">Remover</button>
                            </form>
                        </div>
                    @endif
                </div>
            @empty
                <p class="p-4 text-slate-500 dark:text-slate-400">Sem vagas no momento.</p>
            @endforelse
        </div>

        {{ $pedidos->links() }}
    </div>
</x-app-layout>
