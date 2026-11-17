<?php

namespace App\View\Components;

use App\Models\Banner;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class BannerSlot extends Component
{
    public Collection $banners;

    public function __construct(public string $localizacao)
    {
        $this->banners = Banner::ativosEm($localizacao)->get();
    }

    public function render(): View|Closure|string
    {
        return view('components.banner-slot');
    }
}
