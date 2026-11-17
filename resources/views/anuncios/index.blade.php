<x-app-layout title="Anúncios de serviços" description="Explora anúncios de serviços domésticos por categoria e localização em Portugal.">
    <x-slot name="header">
        <h2 class="font-display font-semibold text-xl text-slate-800 dark:text-slate-200">Anúncios</h2>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <x-banner-slot localizacao="topo" />

        <form method="GET" class="flex flex-wrap gap-3 bg-white dark:bg-slate-800 p-4 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm">
            <select name="categoria_id" class="rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 focus:ring-green-500 focus:border-green-500">
                <option value="">Todas as categorias</option>
                @foreach ($categorias as $categoria)
                    <option value="{{ $categoria->id }}" @selected(request('categoria_id') == $categoria->id)>{{ $categoria->nome }}</option>
                @endforeach
            </select>

            <select name="distrito_id" class="rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 focus:ring-green-500 focus:border-green-500">
                <option value="">Todos os distritos</option>
                @foreach ($distritos as $distrito)
                    <option value="{{ $distrito->id }}" @selected(request('distrito_id') == $distrito->id)>{{ $distrito->nome }}</option>
                @endforeach
            </select>

            <button type="submit" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-filter"></i> Filtrar
            </button>
            <a href="{{ route('anuncios.create') }}" class="btn btn-dark btn-sm ms-auto">
                <i class="fa-solid fa-plus"></i> Publicar anúncio
            </a>
        </form>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @forelse ($anuncios as $anuncio)
                @include('anuncios._card', ['anuncio' => $anuncio])
            @empty
                <div class="col-span-full text-center py-16 text-slate-400 dark:text-slate-500">
                    <i class="fa-solid fa-magnifying-glass text-3xl mb-3"></i>
                    <p>Nenhum anúncio encontrado.</p>
                </div>
            @endforelse
        </div>

        {{ $anuncios->links() }}

        <x-supporters-row />
        <x-banner-slot localizacao="rodape" />
    </div>
</x-app-layout>
