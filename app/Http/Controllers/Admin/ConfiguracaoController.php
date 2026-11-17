<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Configuracao;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConfiguracaoController extends Controller
{
    private const CHAVES = [
        'auto_approval_enabled',
        'google_analytics_id',
        'google_ads_id',
        'dias_expiracao_padrao',
        'dias_aviso_expiracao',
        'contactos_offset',
    ];

    public function edit(): View
    {
        $config = collect(self::CHAVES)->mapWithKeys(fn ($chave) => [$chave => Configuracao::get($chave)]);

        return view('admin.configuracoes.edit', ['config' => $config]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'auto_approval_enabled' => ['sometimes', 'boolean'],
            'google_analytics_id' => ['nullable', 'string', 'max:50'],
            'google_ads_id' => ['nullable', 'string', 'max:50'],
            'dias_expiracao_padrao' => ['nullable', 'integer', 'min:1'],
            'dias_aviso_expiracao' => ['nullable', 'integer', 'min:1'],
            'contactos_offset' => ['nullable', 'integer', 'min:0'],
        ]);

        Configuracao::set('auto_approval_enabled', $request->boolean('auto_approval_enabled') ? '1' : '0');

        foreach (['google_analytics_id', 'google_ads_id', 'dias_expiracao_padrao', 'dias_aviso_expiracao', 'contactos_offset'] as $chave) {
            if (array_key_exists($chave, $data)) {
                Configuracao::set($chave, $data[$chave]);
            }
        }

        return back()->with('status', 'Definições guardadas.');
    }
}
