<x-crm-layout title="Dashboard">
    <div class="space-y-6">
        <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach ([
                ['label' => 'Clientes / profissionais', 'value' => $stats['utilizadores_total'], 'icon' => 'fa-users', 'href' => route('crm.users.index')],
                ['label' => 'Total de anúncios', 'value' => $stats['anuncios_total'], 'icon' => 'fa-rectangle-ad', 'href' => route('crm.anuncios.index')],
                ['label' => 'Anúncios ativos', 'value' => $stats['anuncios_aprovados'], 'icon' => 'fa-circle-check', 'href' => route('crm.anuncios.index', ['status' => 'aprovado'])],
                ['label' => 'Pedidos de orçamento', 'value' => $stats['orcamentos_total'], 'icon' => 'fa-file-invoice-dollar', 'href' => route('crm.orcamentos.index')],
                ['label' => 'Pedidos de recrutamento', 'value' => $stats['recrutamentos_total'], 'icon' => 'fa-briefcase', 'href' => route('crm.recrutamentos.index')],
                ['label' => 'Contactos gerados', 'value' => $stats['contactos_total'], 'icon' => 'fa-chart-line', 'href' => route('crm.contactos.index')],
                ['label' => 'Emails enviados', 'value' => $stats['emails_enviados'], 'icon' => 'fa-envelope', 'href' => route('crm.contactos.index').'#emails'],
            ] as $card)
                <a href="{{ $card['href'] }}" class="card p-5 flex items-center gap-4 hover:shadow-md hover:border-green-300 transition">
                    <span class="icon-circle"><i class="fa-solid {{ $card['icon'] }}"></i></span>
                    <span>
                        <span class="block text-2xl font-bold text-slate-900">{{ number_format($card['value'], 0, ',', '.') }}</span>
                        <span class="block text-sm text-slate-500">{{ $card['label'] }}</span>
                    </span>
                </a>
            @endforeach
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <a href="{{ route('crm.users.index', ['status' => 'pendente']) }}" class="card p-4 hover:shadow-md hover:border-amber-300 transition">
                <p class="text-xl font-bold text-amber-600">{{ $stats['utilizadores_pendentes'] }}</p>
                <p class="text-sm text-slate-500">Clientes pendentes de aprovação</p>
            </a>
            <a href="{{ route('crm.anuncios.index', ['status' => 'pendente']) }}" class="card p-4 hover:shadow-md hover:border-amber-300 transition">
                <p class="text-xl font-bold text-amber-600">{{ $stats['anuncios_pendentes'] }}</p>
                <p class="text-sm text-slate-500">Anúncios pendentes de aprovação</p>
            </a>
            <a href="{{ route('crm.recrutamentos.index') }}" class="card p-4 hover:shadow-md hover:border-green-300 transition">
                <p class="text-xl font-bold text-green-600">{{ $stats['recrutamentos_ativos'] }}</p>
                <p class="text-sm text-slate-500">Vagas de recrutamento ativas</p>
            </a>
        </div>
    </div>
</x-crm-layout>
