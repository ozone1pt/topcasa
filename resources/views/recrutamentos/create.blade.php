<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-200">Publicar vaga</h2>
    </x-slot>

    <div class="py-6 max-w-2xl mx-auto sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('recrutamentos.store') }}" enctype="multipart/form-data" class="bg-white dark:bg-slate-800 rounded-lg shadow p-6 space-y-4">
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
                <x-input-label for="titulo" value="Título da vaga" />
                <x-text-input id="titulo" name="titulo" class="block mt-1 w-full" :value="old('titulo')" required />
            </div>

            <div>
                <x-input-label for="empresa_nome" value="Nome da empresa" />
                <x-text-input id="empresa_nome" name="empresa_nome" class="block mt-1 w-full" :value="old('empresa_nome')" required />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="email" value="Email" />
                    <x-text-input id="email" name="email" type="email" class="block mt-1 w-full" :value="old('email', auth()->user()?->email)" required />
                </div>
                <div>
                    <x-input-label for="telefone" value="Telefone" />
                    <x-text-input id="telefone" name="telefone" class="block mt-1 w-full" :value="old('telefone', auth()->user()?->telefone)" required />
                </div>
            </div>

            <div>
                <x-input-label for="distrito_id" value="Distrito" />
                <select id="distrito_id" name="distrito_id" class="mt-1 block w-full border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 rounded-md" required>
                    <option value="">—</option>
                    @foreach ($distritos as $distrito)
                        <option value="{{ $distrito->id }}" @selected(old('distrito_id') == $distrito->id)>{{ $distrito->nome }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <x-input-label for="descricao" value="Descrição da vaga" />
                <textarea id="descricao" name="descricao" rows="4" class="mt-1 block w-full border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 rounded-md" required>{{ old('descricao') }}</textarea>
            </div>

            <div>
                <x-input-label for="foto" value="Foto/logótipo (opcional)" />
                <input id="foto" name="foto" type="file" accept="image/jpeg,image/png,image/webp" class="mt-1 block w-full text-sm" />
            </div>

            <label class="flex items-center gap-2">
                <input type="checkbox" name="termo_aceite" value="1" required />
                <span class="text-sm text-slate-700 dark:text-slate-300">Aceito os <a href="{{ route('termos') }}" target="_blank" class="underline">termos e condições</a></span>
            </label>

            <x-primary-button>Publicar vaga</x-primary-button>
        </form>
    </div>
</x-app-layout>
