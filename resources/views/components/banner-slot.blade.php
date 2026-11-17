@if ($banners->isNotEmpty())
    <div class="space-y-2">
        @foreach ($banners as $banner)
            <a href="{{ route('banners.click', $banner) }}" class="block">
                @if ($banner->imagem_desktop)
                    <img src="{{ Storage::url($banner->imagem_desktop) }}" alt="{{ $banner->titulo }}" class="hidden sm:block w-full max-h-72 object-cover rounded-md" />
                @endif
                @if ($banner->imagem_mobile)
                    <img src="{{ Storage::url($banner->imagem_mobile) }}" alt="{{ $banner->titulo }}" class="sm:hidden w-full max-h-72 object-cover rounded-md" />
                @endif
                @if (! $banner->imagem_desktop && ! $banner->imagem_mobile)
                    <div class="bg-slate-100 dark:bg-slate-700 rounded-md p-4 text-center">{{ $banner->titulo }}</div>
                @endif
            </a>
            <img src="{{ route('banners.view', $banner) }}" width="1" height="1" alt="" class="hidden" />
        @endforeach
    </div>
@endif
