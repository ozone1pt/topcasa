<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-200">Editar anúncio</h2>
    </x-slot>

    <div class="py-6 max-w-3xl mx-auto sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('anuncios.update', $anuncio) }}" enctype="multipart/form-data" class="bg-white dark:bg-slate-800 rounded-lg shadow p-6 space-y-4">
            @csrf
            @method('PUT')
            @include('anuncios._form', ['anuncio' => $anuncio])
            <x-primary-button>Guardar</x-primary-button>
        </form>
    </div>
</x-app-layout>
