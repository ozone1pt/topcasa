<x-crm-layout title="Alertas enviados">
<div class="space-y-4 space-y-4">
        @if (session('status'))
            <div class="bg-green-100 text-green-800 rounded-md p-3 text-sm">{{ session('status') }}</div>
        @endif

        <a href="{{ route('crm.alertas.create') }}" class="btn btn-primary btn-sm">Novo alerta</a>

        <div class="bg-white dark:bg-slate-800 rounded-lg shadow divide-y dark:divide-slate-700">
            @forelse ($alertas as $alerta)
                <div class="p-4">
                    <p class="font-medium text-slate-900 dark:text-slate-100">{{ $alerta->titulo }}</p>
                    <p class="text-xs text-slate-500">para {{ $alerta->user->username }} · {{ $alerta->tipo }} · {{ $alerta->lida ? 'lido' : 'não lido' }}</p>
                </div>
            @empty
                <p class="p-4 text-slate-500 dark:text-slate-400">Sem alertas enviados.</p>
            @endforelse
        </div>

        {{ $alertas->links() }}
    </div>
</x-crm-layout>
