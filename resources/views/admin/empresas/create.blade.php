<x-crm-layout title="Nova empresa apoiadora">
<div class="space-y-4">
        <form method="POST" action="{{ route('crm.empresas.store') }}" enctype="multipart/form-data" class="bg-white dark:bg-slate-800 rounded-lg shadow p-6 space-y-4">
            @csrf

            @if ($errors->any())
                <div class="bg-red-100 text-red-800 rounded-md p-3 text-sm">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div>
                <x-input-label for="nome" value="Nome" />
                <x-text-input id="nome" name="nome" class="block mt-1 w-full" required />
            </div>
            <div>
                <x-input-label for="url" value="URL (opcional)" />
                <x-text-input id="url" name="url" class="block mt-1 w-full" />
            </div>
            <div>
                <x-input-label for="logo" value="Logótipo" />
                <input id="logo" name="logo" type="file" accept="image/*" class="mt-1 block w-full text-sm" required />
            </div>
            <x-primary-button>Adicionar</x-primary-button>
        </form>
    </div>
</x-crm-layout>
