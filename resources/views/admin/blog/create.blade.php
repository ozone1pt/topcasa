<x-crm-layout title="Criar artigo">
<div class="space-y-4">
        <form method="POST" action="{{ route('crm.blog.store') }}" enctype="multipart/form-data" class="bg-white dark:bg-slate-800 rounded-lg shadow p-6 space-y-4">
            @csrf
            <div>
                <x-input-label for="titulo" value="Título" />
                <x-text-input id="titulo" name="titulo" class="block mt-1 w-full" required />
            </div>
            <div>
                <x-input-label for="conteudo" value="Conteúdo (HTML)" />
                <textarea id="conteudo" name="conteudo" rows="10" class="mt-1 block w-full border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 rounded-md" required></textarea>
            </div>
            <div>
                <x-input-label for="imagem_destaque" value="Imagem de destaque (opcional)" />
                <input id="imagem_destaque" name="imagem_destaque" type="file" accept="image/*" class="mt-1 block w-full text-sm" />
            </div>
            <x-primary-button>Publicar artigo</x-primary-button>
        </form>
    </div>
</x-crm-layout>
