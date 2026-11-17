@php
    $old = fn ($field, $default = null) => old($field, $anuncio?->{$field} ?? $default);
@endphp

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
    <x-text-input id="titulo" name="titulo" class="block mt-1 w-full" :value="$old('titulo')" required />
</div>

<div>
    <x-input-label for="minha_empresa" value="O teu nome ou nome da empresa" />
    <x-text-input id="minha_empresa" name="minha_empresa" class="block mt-1 w-full" :value="$old('minha_empresa', auth()->user()?->nomeCompleto())" required />
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <x-input-label for="categoria_id" value="Categoria" />
        <select id="categoria_id" name="categoria_id" class="mt-1 block w-full border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 rounded-md" required>
            <option value="">—</option>
            @foreach ($categorias as $categoria)
                <option value="{{ $categoria->id }}" @selected($old('categoria_id') == $categoria->id)>{{ $categoria->nome }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <x-input-label for="distrito_id" value="Distrito" />
        <select id="distrito_id" name="distrito_id" class="mt-1 block w-full border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 rounded-md" required>
            <option value="">—</option>
            @foreach ($distritos as $distrito)
                <option value="{{ $distrito->id }}" @selected($old('distrito_id') == $distrito->id)>{{ $distrito->nome }}</option>
            @endforeach
        </select>
    </div>
</div>

<div>
    <x-input-label for="descricao" value="Descrição" />
    <textarea id="descricao" name="descricao" rows="5" class="mt-1 block w-full border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 rounded-md" required>{{ $old('descricao') }}</textarea>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <x-input-label for="contacto" value="Contacto (telefone)" />
        <x-text-input id="contacto" name="contacto" class="block mt-1 w-full" :value="$old('contacto', auth()->user()?->telefone)" required />
    </div>

    <div>
        <x-input-label for="email" value="Email" />
        <x-text-input id="email" name="email" type="email" class="block mt-1 w-full" :value="$old('email', auth()->user()?->email)" required />
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <x-input-label for="youtube_link" value="Link do YouTube (opcional)" />
        <x-text-input id="youtube_link" name="youtube_link" class="block mt-1 w-full" :value="$old('youtube_link')" />
    </div>

    <div>
        <x-input-label for="instagram_link" value="Instagram (opcional)" />
        <x-text-input id="instagram_link" name="instagram_link" placeholder="@oteuinstagram ou link" class="block mt-1 w-full" :value="$old('instagram_link')" />
    </div>
</div>

<div class="space-y-2">
    <label class="flex items-center gap-2">
        <input type="checkbox" name="destacar" value="1" @checked($old('destacar')) />
        <span class="text-sm text-slate-700 dark:text-slate-300">Destacar anúncio</span>
    </label>
    <label class="flex items-center gap-2">
        <input type="checkbox" name="certificacao_profissional" value="1" @checked($old('certificacao_profissional')) />
        <span class="text-sm text-slate-700 dark:text-slate-300">Tenho certificação profissional</span>
    </label>
    <label class="flex items-center gap-2">
        <input type="checkbox" name="seguro_responsabilidade_civil" value="1" @checked($old('seguro_responsabilidade_civil')) />
        <span class="text-sm text-slate-700 dark:text-slate-300">Tenho seguro de responsabilidade civil</span>
    </label>
</div>

@if ($anuncio && $anuncio->galeria->isNotEmpty())
    <div>
        <x-input-label value="Imagens atuais" />
        <div class="mt-1">
            {{-- No nested <form>s here: the old per-image delete forms lived inside the
                 page form, which made the browser close the outer form early and left the
                 "Guardar" button doing nothing. --}}
            <x-galeria-editavel :anuncio="$anuncio" />
        </div>
    </div>
@endif

<div>
    <x-input-label value="Imagens (até 5, máx. 8MB cada)" />
    <div class="mt-1 flex flex-wrap gap-3" id="imagens-preview-wrap">
        <label for="imagens" class="w-24 h-24 shrink-0 flex flex-col items-center justify-center gap-1 border-2 border-dashed border-slate-300 dark:border-slate-600 rounded-lg cursor-pointer hover:border-green-500 hover:bg-green-50 dark:hover:bg-slate-700 transition text-slate-400 hover:text-green-600">
            <i class="fa-solid fa-plus text-2xl"></i>
            <span class="text-xs text-center px-1 leading-tight">Adicionar fotos</span>
        </label>
    </div>
    <input id="imagens" name="imagens[]" type="file" multiple accept="image/jpeg,image/png,image/webp" class="hidden" />
    <p class="text-xs text-slate-500 dark:text-slate-400 mt-2" id="imagens-count-label">📸 Anúncios com fotos têm muito mais visualizações. Toca no quadrado com o + para adicionares as tuas.</p>
</div>

<script>
    (function () {
        const input = document.getElementById('imagens');
        const wrap = document.getElementById('imagens-preview-wrap');
        const addButton = document.querySelector('label[for="imagens"]');
        const countLabel = document.getElementById('imagens-count-label');

        input.addEventListener('change', () => {
            wrap.querySelectorAll('[data-preview]').forEach((el) => el.remove());

            const files = Array.from(input.files).slice(0, 5);

            files.forEach((file) => {
                const url = URL.createObjectURL(file);
                const thumb = document.createElement('div');
                thumb.dataset.preview = '1';
                thumb.className = 'w-24 h-24 shrink-0 rounded-lg overflow-hidden border border-slate-200 dark:border-slate-700';
                thumb.innerHTML = `<img src="${url}" class="w-full h-full object-cover" />`;
                wrap.insertBefore(thumb, addButton);
            });

            countLabel.textContent = files.length
                ? `${files.length} imagem(ns) selecionada(s) de 5.`
                : '📸 Anúncios com fotos têm muito mais visualizações. Toca no quadrado com o + para adicionares as tuas.';
        });
    })();
</script>
