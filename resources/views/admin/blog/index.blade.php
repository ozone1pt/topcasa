<x-crm-layout title="Gestão do blog">
<div class="space-y-4 space-y-4">
        @if (session('status'))
            <div class="bg-green-100 text-green-800 rounded-md p-3 text-sm">{{ session('status') }}</div>
        @endif

        <a href="{{ route('crm.blog.create') }}" class="btn btn-primary btn-sm">Criar artigo manualmente</a>

        <div class="bg-white dark:bg-slate-800 rounded-lg shadow divide-y dark:divide-slate-700">
            @forelse ($posts as $post)
                <div class="p-4 flex items-center justify-between gap-4">
                    <div>
                        <p class="font-medium text-slate-900 dark:text-slate-100">{{ $post->titulo }}</p>
                        <p class="text-xs text-slate-500">estado: {{ $post->status }} @if ($post->topico_fonte) · gerado por IA @endif</p>
                    </div>
                    <div class="flex gap-2">
                        @if ($post->status === 'pendente')
                            <form method="POST" action="{{ route('crm.blog.publicar', $post) }}">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-sm">Publicar</button>
                            </form>
                        @endif
                        <form method="POST" action="{{ route('crm.blog.destroy', $post) }}" onsubmit="return confirm('Remover este artigo?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Remover</button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="p-4 text-slate-500 dark:text-slate-400">Sem artigos.</p>
            @endforelse
        </div>

        {{ $posts->links() }}
    </div>
</x-crm-layout>
