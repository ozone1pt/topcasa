<x-crm-layout title="Banners">
<div class="space-y-4 space-y-4">
        @if (session('status'))
            <div class="bg-green-100 text-green-800 rounded-md p-3 text-sm">{{ session('status') }}</div>
        @endif

        <a href="{{ route('crm.banners.create') }}" class="btn btn-primary btn-sm">Novo banner</a>

        <div class="bg-white dark:bg-slate-800 rounded-lg shadow divide-y dark:divide-slate-700">
            @forelse ($banners as $banner)
                <div class="p-4 flex items-center justify-between gap-4">
                    <div>
                        <p class="font-medium text-slate-900 dark:text-slate-100">{{ $banner->titulo }}</p>
                        <p class="text-xs text-slate-500">
                            {{ $banner->localizacao }} · {{ $banner->ativo ? 'ativo' : 'inativo' }}
                            · {{ $banner->views }} views · {{ $banner->clicks }} clicks
                        </p>
                    </div>
                    <div class="flex gap-3">
                        <a href="{{ route('crm.banners.edit', $banner) }}" class="text-sm underline">Editar</a>
                        <form method="POST" action="{{ route('crm.banners.destroy', $banner) }}" onsubmit="return confirm('Remover este banner?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-sm text-red-600 underline">Remover</button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="p-4 text-slate-500 dark:text-slate-400">Sem banners.</p>
            @endforelse
        </div>
    </div>
</x-crm-layout>
