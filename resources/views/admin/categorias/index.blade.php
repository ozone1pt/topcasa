<x-crm-layout title="Categorias">
<div class="space-y-4 space-y-4">
        @if (session('status'))
            <div class="bg-green-100 text-green-800 rounded-md p-3 text-sm">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('crm.categorias.store') }}" class="flex flex-wrap gap-3 bg-white dark:bg-slate-800 p-4 rounded-lg shadow">
            @csrf
            <input type="text" name="nome" placeholder="Nome da categoria" required class="rounded-md border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200" />
            <input type="text" name="icone" placeholder="Ícone (ex: fa-tools)" class="rounded-md border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200" />
            <x-primary-button>Adicionar</x-primary-button>
        </form>

        <div class="bg-white dark:bg-slate-800 rounded-lg shadow divide-y dark:divide-slate-700">
            @foreach ($categorias as $categoria)
                <div class="p-4 flex items-center justify-between gap-4">
                    <span class="text-slate-900 dark:text-slate-100">{{ $categoria->nome }} <span class="text-xs text-slate-500">({{ $categoria->icone }})</span></span>
                    <div class="flex gap-3">
                        <form method="POST" action="{{ route('crm.categorias.toggle', $categoria) }}">
                            @csrf
                            <button type="submit" class="text-sm underline">{{ $categoria->status ? 'Desativar' : 'Ativar' }}</button>
                        </form>
                        <form method="POST" action="{{ route('crm.categorias.destroy', $categoria) }}" onsubmit="return confirm('Remover categoria?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-sm text-red-600 underline">Remover</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-crm-layout>
