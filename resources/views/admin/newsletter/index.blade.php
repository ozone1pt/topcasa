<x-crm-layout title="Newsletter">
<div class="space-y-4 space-y-4">
        @if (session('status'))
            <div class="bg-green-100 text-green-800 rounded-md p-3 text-sm">{{ session('status') }}</div>
        @endif

        <div class="bg-white dark:bg-slate-800 rounded-lg shadow p-4">
            <p class="text-sm text-slate-600 dark:text-slate-400">{{ $totalAtivos }} subscritores ativos.</p>
        </div>

        <form method="POST" action="{{ route('crm.newsletter.enviar') }}" class="bg-white dark:bg-slate-800 rounded-lg shadow p-6 space-y-4">
            @csrf
            <div>
                <x-input-label for="assunto" value="Assunto" />
                <x-text-input id="assunto" name="assunto" class="block mt-1 w-full" required />
            </div>
            <div>
                <x-input-label for="corpo" value="Mensagem" />
                <textarea id="corpo" name="corpo" rows="6" class="mt-1 block w-full border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 rounded-md" required></textarea>
            </div>
            <x-primary-button>Enviar campanha</x-primary-button>
        </form>

        <div class="bg-white dark:bg-slate-800 rounded-lg shadow divide-y dark:divide-slate-700">
            @foreach ($subscribers as $subscriber)
                <div class="p-3 text-sm text-slate-700 dark:text-slate-300 flex justify-between">
                    <span>{{ $subscriber->email }}</span>
                    <span class="text-xs text-slate-500">{{ $subscriber->ativo ? 'ativo' : 'inativo' }}</span>
                </div>
            @endforeach
        </div>

        {{ $subscribers->links() }}
    </div>
</x-crm-layout>
