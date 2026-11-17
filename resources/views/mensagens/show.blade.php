<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-200">{{ $mensagem->anuncio->titulo }}</h2>
    </x-slot>

    <div class="py-6 max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-4">
        @if (session('status'))
            <div class="bg-green-100 text-green-800 rounded-md p-3 text-sm">{{ session('status') }}</div>
        @endif

        <div class="bg-white dark:bg-slate-800 rounded-lg shadow p-6 space-y-4">
            <div>
                <p class="text-sm text-slate-500">{{ $mensagem->remetenteNome() }} · {{ $mensagem->created_at->format('d/m/Y H:i') }}</p>
                <p class="text-slate-800 dark:text-slate-200">{{ $mensagem->mensagem }}</p>
            </div>

            @foreach ($mensagem->respostas as $resposta)
                <div class="border-t dark:border-slate-700 pt-3">
                    <p class="text-sm text-slate-500">{{ $resposta->remetenteNome() }} · {{ $resposta->created_at->format('d/m/Y H:i') }}</p>
                    <p class="text-slate-800 dark:text-slate-200">{{ $resposta->mensagem }}</p>
                </div>
            @endforeach

            <form method="POST" action="{{ route('mensagens.reply', $mensagem) }}" class="border-t dark:border-slate-700 pt-4 space-y-2">
                @csrf
                <textarea name="mensagem" rows="3" class="block w-full border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 rounded-md" required></textarea>
                <x-primary-button>Responder</x-primary-button>
            </form>

            <form method="POST" action="{{ route('mensagens.destroy', $mensagem) }}" onsubmit="return confirm('Remover esta conversa?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-sm text-red-600 underline">Remover conversa</button>
            </form>
        </div>
    </div>
</x-app-layout>
