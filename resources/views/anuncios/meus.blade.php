<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-200">Os meus anúncios</h2>
    </x-slot>

    <div class="py-6 max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
        @if (session('status'))
            <div class="bg-green-100 text-green-800 rounded-md p-3 text-sm">{{ session('status') }}</div>
        @endif

        <div class="bg-white dark:bg-slate-800 rounded-lg shadow divide-y dark:divide-slate-700">
            @forelse ($anuncios as $anuncio)
                <div class="p-4 flex items-center justify-between gap-4">
                    <div>
                        <a href="{{ route('anuncios.show', $anuncio) }}" class="font-medium text-slate-900 dark:text-slate-100">{{ $anuncio->titulo }}</a>
                        <p class="text-sm text-slate-500 dark:text-slate-400">{{ $anuncio->categoria?->nome }} · estado: {{ $anuncio->status }}</p>
                    </div>
                    <div class="flex gap-3 items-center">
                        @if ($anuncio->status === 'expirado')
                            <form method="POST" action="{{ route('anuncios.reativar', $anuncio) }}">
                                @csrf
                                <button type="submit" class="text-sm text-green-700 underline">Reativar</button>
                            </form>
                        @endif
                        <a href="{{ route('anuncios.edit', $anuncio) }}" class="text-sm underline">Editar</a>
                    </div>
                </div>
            @empty
                <p class="p-4 text-slate-500 dark:text-slate-400">Ainda não publicaste nenhum anúncio.</p>
            @endforelse
        </div>

        {{ $anuncios->links() }}
    </div>
</x-app-layout>
