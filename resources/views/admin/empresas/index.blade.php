<x-crm-layout title="Empresas apoiadoras">
<div class="space-y-4 space-y-4">
        @if (session('status'))
            <div class="bg-green-100 text-green-800 rounded-md p-3 text-sm">{{ session('status') }}</div>
        @endif

        <a href="{{ route('crm.empresas.create') }}" class="btn btn-primary btn-sm">Nova empresa</a>

        <div class="bg-white dark:bg-slate-800 rounded-lg shadow divide-y dark:divide-slate-700">
            @forelse ($empresas as $empresa)
                <div class="p-4 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <img src="{{ Storage::url($empresa->logo) }}" class="h-8 w-8 object-contain" />
                        <span class="font-medium text-slate-900 dark:text-slate-100">{{ $empresa->nome }}</span>
                    </div>
                    <form method="POST" action="{{ route('crm.empresas.destroy', $empresa) }}" onsubmit="return confirm('Remover esta empresa?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-sm text-red-600 underline">Remover</button>
                    </form>
                </div>
            @empty
                <p class="p-4 text-slate-500 dark:text-slate-400">Sem empresas apoiadoras.</p>
            @endforelse
        </div>
    </div>
</x-crm-layout>
