{{-- Attached photo of a pedido de orçamento / vaga, shown the same way as an
     anúncio's gallery thumbnails in the CRM list (click opens it full size). --}}
<div class="mb-3" x-data="{ erro: false }">
    <div class="relative shrink-0 w-20 h-20 rounded-lg overflow-hidden border border-slate-200 bg-slate-100">
        <a href="{{ $url }}" target="_blank" rel="noopener" class="block w-full h-full" x-show="! erro" title="Abrir imagem">
            <img src="{{ $url }}" x-on:error="erro = true" loading="lazy" class="w-full h-full object-cover" alt="Imagem anexada" />
        </a>
        <div x-show="erro" x-cloak class="w-full h-full flex items-center justify-center text-center text-slate-400 p-1" style="font-size:10px;line-height:1.2">Imagem indisponível</div>
    </div>
</div>
