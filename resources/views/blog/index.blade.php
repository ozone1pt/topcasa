<x-app-layout title="Blog" description="Dicas, guias e novidades sobre serviços domésticos, manutenção e remodelação da tua casa.">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-200">Blog</h2>
    </x-slot>

    <div class="py-6 max-w-5xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse ($posts as $post)
            <a href="{{ route('blog.show', $post) }}" class="block bg-white dark:bg-slate-800 rounded-lg shadow overflow-hidden hover:shadow-md transition">
                @if ($post->imagem_destaque)
                    <img src="{{ Storage::url($post->imagem_destaque) }}" class="w-full aspect-video object-cover" />
                @endif
                <div class="p-4">
                    <h3 class="font-semibold text-slate-900 dark:text-slate-100">{{ $post->titulo }}</h3>
                    <p class="text-xs text-slate-500 mt-1">{{ $post->publicado_em?->format('d/m/Y') }}</p>
                </div>
            </a>
        @empty
            <p class="text-slate-500 dark:text-slate-400 col-span-full">Ainda sem artigos publicados.</p>
        @endforelse
    </div>

    <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">{{ $posts->links() }}</div>
</x-app-layout>
