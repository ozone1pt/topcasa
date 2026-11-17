<x-app-layout title="Pedidos de orçamento" description="Consulta pedidos de orçamento de clientes em Portugal e contacta quem precisa dos teus serviços.">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-200">Pedidos de orçamento</h2>
    </x-slot>

    <div class="py-6 max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-4">
        @if (session('status'))
            <div class="bg-green-100 text-green-800 rounded-md p-3 text-sm">{{ session('status') }}</div>
        @endif

        <a href="{{ route('orcamentos.create') }}" class="btn btn-primary btn-sm">Pedir orçamento</a>

        <div class="bg-white dark:bg-slate-800 rounded-lg shadow divide-y dark:divide-slate-700">
            @forelse ($pedidos as $pedido)
                <div
                    class="p-4"
                    x-data="{
                        contacto: null,
                        async verContacto() {
                            const res = await fetch('{{ route('orcamentos.desbloquear', $pedido) }}', {
                                method: 'POST',
                                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                            });
                            this.contacto = await res.json();
                        },
                    }"
                >
                    <div class="flex justify-between">
                        <h3 class="font-medium text-slate-900 dark:text-slate-100">{{ $pedido->titulo ?: 'Pedido de orçamento' }}</h3>
                        <span class="text-xs text-slate-500 text-right">{{ $pedido->distrito?->nome }} · {{ $pedido->status }}</span>
                    </div>
                    <p class="text-xs text-slate-400 mt-0.5"><i class="fa-regular fa-calendar"></i> Publicado em {{ $pedido->created_at?->timezone('Europe/Lisbon')->format('d/m/Y') }} às {{ $pedido->created_at?->timezone('Europe/Lisbon')->format('H:i') }}</p>
                    <p class="text-sm text-slate-600 dark:text-slate-400">{{ Str::limit($pedido->descricao, 150) }}</p>

                    <p x-show="! contacto" class="mt-2">
                        <button type="button" @click="verContacto" class="text-sm underline">Ver contacto</button>
                    </p>
                    <p x-cloak x-show="contacto" class="text-sm text-green-700 mt-2">
                        <span x-text="contacto?.nome"></span> · <span x-text="contacto?.telefone"></span> · <span x-text="contacto?.email"></span>
                    </p>
                </div>
            @empty
                <p class="p-4 text-slate-500 dark:text-slate-400">Sem pedidos de orçamento.</p>
            @endforelse
        </div>

        {{ $pedidos->links() }}
    </div>
</x-app-layout>
