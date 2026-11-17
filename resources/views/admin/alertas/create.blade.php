<x-crm-layout title="Novo alerta">
<div class="space-y-4">
        <form method="POST" action="{{ route('crm.alertas.store') }}" class="bg-white dark:bg-slate-800 rounded-lg shadow p-6 space-y-4" x-data="{ destinatarios: 'todos' }">
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
                <x-input-label for="titulo" value="Título" />
                <x-text-input id="titulo" name="titulo" class="block mt-1 w-full" required />
            </div>
            <div>
                <x-input-label for="mensagem" value="Mensagem" />
                <textarea id="mensagem" name="mensagem" rows="4" class="mt-1 block w-full border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 rounded-md" required></textarea>
            </div>
            <div>
                <x-input-label for="tipo" value="Tipo" />
                <select id="tipo" name="tipo" class="mt-1 block w-full border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 rounded-md">
                    @foreach (['info', 'aviso', 'sucesso', 'erro'] as $tipo)
                        <option value="{{ $tipo }}">{{ ucfirst($tipo) }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <x-input-label value="Destinatários" />
                <label class="flex items-center gap-2 mt-1">
                    <input type="radio" name="destinatarios" value="todos" x-model="destinatarios" checked />
                    <span class="text-sm text-slate-700 dark:text-slate-300">Todos os utilizadores aprovados</span>
                </label>
                <label class="flex items-center gap-2">
                    <input type="radio" name="destinatarios" value="selecionados" x-model="destinatarios" />
                    <span class="text-sm text-slate-700 dark:text-slate-300">Selecionados</span>
                </label>
            </div>

            <div x-show="destinatarios === 'selecionados'" x-cloak>
                <select name="user_ids[]" multiple class="mt-1 block w-full border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 rounded-md" size="8">
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}">{{ $user->username }} ({{ $user->email }})</option>
                    @endforeach
                </select>
            </div>

            <x-primary-button>Enviar alerta</x-primary-button>
        </form>
    </div>
</x-crm-layout>
