<x-crm-layout title="Definições">
<div class="space-y-4">
        @if (session('status'))
            <div class="bg-green-100 text-green-800 rounded-md p-3 text-sm mb-4">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('crm.configuracoes.update') }}" class="bg-white dark:bg-slate-800 rounded-lg shadow p-6 space-y-4">
            @csrf
            @method('PUT')

            <label class="flex items-center gap-2">
                <input type="checkbox" name="auto_approval_enabled" value="1" @checked($config['auto_approval_enabled'] === '1') />
                <span class="text-sm text-slate-700 dark:text-slate-300">Aprovação automática de anúncios (filtro de palavras)</span>
            </label>

            <div>
                <x-input-label for="google_analytics_id" value="Google Analytics ID" />
                <x-text-input id="google_analytics_id" name="google_analytics_id" class="block mt-1 w-full" :value="$config['google_analytics_id']" placeholder="G-XXXXXXXXXX" />
            </div>

            <div>
                <x-input-label for="google_ads_id" value="Google Ads ID" />
                <x-text-input id="google_ads_id" name="google_ads_id" class="block mt-1 w-full" :value="$config['google_ads_id']" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <x-input-label for="dias_expiracao_padrao" value="Dias até expiração de anúncio" />
                    <x-text-input id="dias_expiracao_padrao" name="dias_expiracao_padrao" type="number" class="block mt-1 w-full" :value="$config['dias_expiracao_padrao'] ?? 30" />
                </div>
                <div>
                    <x-input-label for="dias_aviso_expiracao" value="Dias de aviso antes de expirar" />
                    <x-text-input id="dias_aviso_expiracao" name="dias_aviso_expiracao" type="number" class="block mt-1 w-full" :value="$config['dias_aviso_expiracao'] ?? 5" />
                </div>
            </div>

            <div>
                <x-input-label for="contactos_offset" value="Contactos efetuados (nº base a somar à homepage)" />
                <x-text-input id="contactos_offset" name="contactos_offset" type="number" class="block mt-1 w-full" :value="$config['contactos_offset'] ?? 0" />
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">O número mostrado na homepage em "Contactos Efetuados" é este valor + o total real de mensagens enviadas no site.</p>
            </div>

            <x-primary-button>Guardar</x-primary-button>
        </form>
    </div>
</x-crm-layout>
