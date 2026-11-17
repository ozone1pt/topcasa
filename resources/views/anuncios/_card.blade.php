<a href="{{ route('anuncios.show', $anuncio) }}" class="group block card overflow-hidden hover:shadow-lg hover:border-green-300 dark:hover:border-green-600 transition">
    <div class="aspect-video bg-slate-100 dark:bg-slate-700 relative overflow-hidden">
        @if ($anuncio->galeria->first())
            <img src="{{ Storage::url($anuncio->galeria->first()->imagem) }}" alt="{{ $anuncio->titulo }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300" />
        @else
            <div class="w-full h-full flex items-center justify-center bg-slate-50 dark:bg-slate-800">
                <img src="{{ asset('images/logo-topcasa-icon-square.png') }}" alt="TopCasa" class="w-16 h-16 opacity-50" />
            </div>
        @endif
    </div>
    <div class="p-4">
        <p class="text-xs font-medium text-green-600 dark:text-green-400 uppercase tracking-wide">{{ $anuncio->categoria?->nome }}</p>
        <h3 class="font-semibold text-slate-900 dark:text-slate-100 mt-0.5 truncate">{{ $anuncio->titulo }}</h3>
        @if ($anuncio->nomeProfissional() !== '')
            <p class="text-sm text-slate-600 dark:text-slate-300 mt-0.5 truncate"><i class="fa-solid fa-user text-xs text-slate-400"></i> {{ $anuncio->nomeProfissional() }}</p>
        @endif
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 flex items-center gap-1">
            <i class="fa-solid fa-location-dot text-xs"></i> {{ $anuncio->concelho?->nome ?? $anuncio->distrito?->nome }}
        </p>
    </div>
</a>
