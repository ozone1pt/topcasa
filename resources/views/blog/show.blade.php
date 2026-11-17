<x-app-layout :title="$post->titulo" :description="\Illuminate\Support\Str::limit(strip_tags($post->conteudo), 155)">
    <x-slot name="header">
        <h2 class="font-display font-semibold text-xl text-slate-800 dark:text-slate-200">{{ $post->titulo }}</h2>
    </x-slot>

    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => $post->titulo,
            'datePublished' => optional($post->publicado_em)->toIso8601String(),
            'image' => $post->imagem_destaque ? Storage::url($post->imagem_destaque) : null,
            'publisher' => ['@type' => 'Organization', 'name' => 'TopCasa'],
        ]) !!}
    </script>

    <div class="py-6 max-w-3xl mx-auto sm:px-6 lg:px-8">
        <article class="bg-white dark:bg-slate-800 rounded-lg shadow p-6">
            @if ($post->imagem_destaque)
                <img src="{{ Storage::url($post->imagem_destaque) }}" class="w-full aspect-video object-cover rounded-md mb-4" />
            @endif
            <div class="prose dark:prose-invert max-w-none">{!! $post->conteudo !!}</div>
        </article>
    </div>
</x-app-layout>
