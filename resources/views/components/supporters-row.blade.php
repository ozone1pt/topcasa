@if ($empresas->isNotEmpty())
    <div class="flex flex-wrap items-center gap-6 py-4">
        @foreach ($empresas as $empresa)
            <a href="{{ $empresa->url ?? '#' }}" target="_blank" rel="noopener" title="{{ $empresa->nome }}">
                <img src="{{ Storage::url($empresa->logo) }}" alt="{{ $empresa->nome }}" class="h-10 object-contain grayscale hover:grayscale-0 transition" />
            </a>
        @endforeach
    </div>
@endif
