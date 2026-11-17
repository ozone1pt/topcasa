<?php

namespace App\View\Components;

use App\Models\EmpresaApoiadora;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class SupportersRow extends Component
{
    public Collection $empresas;

    public function __construct()
    {
        $this->empresas = EmpresaApoiadora::ativas()->get();
    }

    public function render(): View|Closure|string
    {
        return view('components.supporters-row');
    }
}
