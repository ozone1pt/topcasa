@props(['anuncio', 'remover' => true, 'tamanho' => 'w-24 h-24'])

{{-- Thumbnails of an anúncio's gallery with rotate (and optionally delete) buttons.
     Everything is done with fetch() so it works inside another <form> without nesting forms. --}}
@php
    $imagens = $anuncio->galeria->map(fn ($i) => [
        'id' => $i->id,
        'url' => Storage::url($i->imagem),
        'rodar' => route('anuncios.imagem.rodar', $i),
        'apagar' => route('anuncios.imagem.destroy', $i),
        'erro' => false,
    ])->values();
@endphp

<div
    x-init="$nextTick(() => {
        if (window.Sortable && $refs.lista) {
            Sortable.create($refs.lista, {
                animation: 150,
                draggable: '[data-id]',
                filter: 'button',
                preventOnFilter: false,
                delay: 150,
                delayOnTouchOnly: true,
                ghostClass: 'opacity-40',
                onEnd: () => ordenar([...$refs.lista.querySelectorAll('[data-id]')].map((el) => Number(el.dataset.id))),
            });
        }
    })"
    x-data="{
        ordenarUrl: '{{ route('anuncios.imagens.ordenar') }}',
        imagens: {{ Illuminate\Support\Js::from($imagens) }},
        ocupado: null,
        erro: '',
        guardado: false,
        async pedir(img, url, opcoes) {
            this.ocupado = img.id;
            this.erro = '';
            try {
                const res = await fetch(url, {
                    ...opcoes,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                });
                if (! res.ok) { throw new Error(); }
                return await res.json();
            } catch (e) {
                this.erro = 'Não foi possível concluir a ação. Tenta novamente.';
                return null;
            } finally {
                this.ocupado = null;
            }
        },
        async ordenar(ids) {
            // keep the Alpine list in the same order the images were dropped in
            this.imagens = ids.map((id) => this.imagens.find((i) => i.id === id)).filter(Boolean);
            this.erro = '';
            this.guardado = false;
            try {
                const res = await fetch(this.ordenarUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ ids }),
                });
                if (! res.ok) { throw new Error(); }
                this.guardado = true;
                setTimeout(() => { this.guardado = false; }, 2500);
            } catch (e) {
                this.erro = 'Não foi possível guardar a nova ordem. Atualiza a página e tenta de novo.';
            }
        },
        async rodar(img, sentido) {
            const data = await this.pedir(img, img.rodar, { method: 'POST', body: JSON.stringify({ sentido }) });
            if (data) { img.erro = false; img.url = data.url; }
        },
        async apagar(img) {
            if (! confirm('Remover esta imagem?')) { return; }
            const data = await this.pedir(img, img.apagar, { method: 'DELETE' });
            if (data) { this.imagens = this.imagens.filter((i) => i.id !== img.id); }
        },
    }"
>
    <div class="flex flex-wrap gap-2" x-ref="lista">
        <template x-for="img in imagens" :key="img.id">
            <div :data-id="img.id" class="relative shrink-0 cursor-grab active:cursor-grabbing {{ $tamanho }} rounded-lg overflow-hidden border border-slate-200 bg-slate-100" :class="ocupado === img.id && 'opacity-50'">
                <a :href="img.url" target="_blank" rel="noopener" class="block w-full h-full" x-show="! img.erro">
                    <img :src="img.url" x-on:error="img.erro = true" draggable="false" class="w-full h-full object-cover" alt="" />
                </a>
                <span x-show="imagens[0] && imagens[0].id === img.id" x-cloak class="absolute top-1 left-1 bg-green-600 text-white text-[10px] font-semibold rounded px-1.5 py-0.5 leading-none">Capa</span>
                <div x-show="img.erro" x-cloak class="w-full h-full flex items-center justify-center text-center text-[10px] leading-tight text-slate-400 p-1">Imagem indisponível</div>

                <div class="absolute inset-x-0 bottom-0 flex bg-black/60 text-white">
                    <button type="button" @click="rodar(img, 'esquerda')" :disabled="ocupado !== null" title="Rodar para a esquerda" class="flex-1 py-1 text-xs hover:bg-black/80">
                        <i class="fa-solid fa-rotate-left"></i>
                    </button>
                    <button type="button" @click="rodar(img, 'direita')" :disabled="ocupado !== null" title="Rodar para a direita" class="flex-1 py-1 text-xs hover:bg-black/80">
                        <i class="fa-solid fa-rotate-right"></i>
                    </button>
                </div>

                @if ($remover)
                    <button type="button" @click="apagar(img)" :disabled="ocupado !== null" title="Remover imagem" class="absolute top-1 right-1 bg-red-600 text-white rounded-full w-6 h-6 text-xs leading-none">×</button>
                @endif
            </div>
        </template>
    </div>
    <p x-show="imagens.length > 1" x-cloak class="text-xs text-slate-500 mt-2">Arrasta as imagens para mudar a ordem. A primeira é a capa do anúncio.</p>
    <p x-show="guardado" x-cloak class="text-xs text-green-700 mt-1">Ordem guardada.</p>
    <p x-show="erro" x-cloak x-text="erro" class="text-xs text-red-600 mt-1"></p>
</div>
