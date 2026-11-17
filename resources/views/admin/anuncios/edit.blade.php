<x-crm-layout title="Editar anúncio">
    <div class="max-w-3xl">
        <a href="{{ route('crm.anuncios.index') }}" class="text-sm text-slate-500 hover:underline">&larr; Voltar aos anúncios</a>

        <form method="POST" action="{{ route('crm.anuncios.update', $anuncio) }}" enctype="multipart/form-data" class="mt-4 bg-white rounded-lg shadow p-6 space-y-6">
            @csrf
            @method('PUT')
            @include('anuncios._form', ['anuncio' => $anuncio])
            <x-primary-button>Guardar alterações</x-primary-button>
        </form>
    </div>
</x-crm-layout>
