<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-200">Favoritos</h2>
    </x-slot>

    <div class="py-6 max-w-5xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse ($anuncios as $anuncio)
            <a href="{{ route('anuncios.show', $anuncio) }}" class="block bg-white dark:bg-slate-800 rounded-lg shadow p-4 hover:shadow-md transition">
                <h3 class="font-semibold text-slate-900 dark:text-slate-100">{{ $anuncio->titulo }}</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400">{{ $anuncio->categoria?->nome }}</p>
            </a>
        @empty
            <p class="text-slate-500 dark:text-slate-400 col-span-full">Sem favoritos ainda.</p>
        @endforelse
    </div>
</x-app-layout>
