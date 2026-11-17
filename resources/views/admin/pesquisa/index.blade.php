<x-crm-layout title="Pesquisa">
    <div class="space-y-6 max-w-4xl">
        @if (mb_strlen($q) < 2)
            <p class="text-slate-500">Escreve pelo menos 2 letras na barra de pesquisa para procurar anúncios, clientes, orçamentos e recrutamentos.</p>
        @else
            <p class="text-sm text-slate-500">
                {{ $total }} resultado(s) para <strong class="text-slate-800">"{{ $q }}"</strong>
                (máx. {{ 15 }} por secção)
            </p>

            {{-- Anúncios --}}
            <section class="bg-white rounded-lg shadow divide-y">
                <h2 class="p-4 font-semibold text-slate-900"><i class="fa-solid fa-rectangle-ad text-green-600 mr-1"></i> Anúncios ({{ $resultados['anuncios']->count() }})</h2>
                @forelse ($resultados['anuncios'] as $anuncio)
                    <div class="p-4 flex flex-wrap items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-medium text-slate-900 truncate">{{ $anuncio->titulo }}</p>
                            <p class="text-xs text-slate-500">
                                {{ $anuncio->minha_empresa }} · {{ $anuncio->categoria?->nome }} · {{ $anuncio->contacto }} · {{ $anuncio->email }} · {{ ucfirst($anuncio->status) }}
                            </p>
                        </div>
                        <div class="flex gap-2 shrink-0">
                            <a href="{{ route('crm.anuncios.edit', $anuncio) }}" class="btn btn-outline btn-sm">Editar</a>
                            <a href="{{ route('anuncios.show', $anuncio) }}" target="_blank" rel="noopener" class="btn btn-outline btn-sm">Ver anúncio</a>
                        </div>
                    </div>
                @empty
                    <p class="p-4 text-sm text-slate-500">Nenhum anúncio.</p>
                @endforelse
            </section>

            {{-- Clientes --}}
            <section class="bg-white rounded-lg shadow divide-y">
                <h2 class="p-4 font-semibold text-slate-900"><i class="fa-solid fa-users text-green-600 mr-1"></i> Clientes ({{ $resultados['clientes']->count() }})</h2>
                @forelse ($resultados['clientes'] as $cliente)
                    <div class="p-4 flex flex-wrap items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-medium text-slate-900 truncate">{{ $cliente->nomeCompleto() ?: $cliente->username }}</p>
                            <p class="text-xs text-slate-500">
                                {{ $cliente->email }} · {{ $cliente->telefone }} · {{ $cliente->anuncios_count }} anúncio(s) · {{ ucfirst($cliente->status) }}
                            </p>
                        </div>
                        <a href="{{ route('crm.users.show', $cliente) }}" class="btn btn-outline btn-sm shrink-0">Ver ficha</a>
                    </div>
                @empty
                    <p class="p-4 text-sm text-slate-500">Nenhum cliente.</p>
                @endforelse
            </section>

            {{-- Orçamentos --}}
            <section class="bg-white rounded-lg shadow divide-y">
                <h2 class="p-4 font-semibold text-slate-900"><i class="fa-solid fa-file-invoice-dollar text-green-600 mr-1"></i> Orçamentos ({{ $resultados['orcamentos']->count() }})</h2>
                @forelse ($resultados['orcamentos'] as $pedido)
                    <div class="p-4 flex flex-wrap items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-medium text-slate-900 truncate">{{ $pedido->titulo ?: 'Pedido de orçamento' }}</p>
                            <p class="text-xs text-slate-500">{{ $pedido->nome }} · {{ $pedido->telefone }} · {{ $pedido->email }} · {{ ucfirst($pedido->status) }}</p>
                        </div>
                        <a href="{{ route('crm.orcamentos.index', ['busca' => $pedido->nome ?: $pedido->titulo]) }}" class="btn btn-outline btn-sm shrink-0">Abrir</a>
                    </div>
                @empty
                    <p class="p-4 text-sm text-slate-500">Nenhum orçamento.</p>
                @endforelse
            </section>

            {{-- Recrutamentos --}}
            <section class="bg-white rounded-lg shadow divide-y">
                <h2 class="p-4 font-semibold text-slate-900"><i class="fa-solid fa-briefcase text-green-600 mr-1"></i> Recrutamentos ({{ $resultados['recrutamentos']->count() }})</h2>
                @forelse ($resultados['recrutamentos'] as $pedido)
                    <div class="p-4 flex flex-wrap items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-medium text-slate-900 truncate">{{ $pedido->titulo }}</p>
                            <p class="text-xs text-slate-500">{{ $pedido->empresa_nome }} · {{ $pedido->telefone }} · {{ $pedido->email }} · {{ ucfirst($pedido->status) }}</p>
                        </div>
                        <a href="{{ route('crm.recrutamentos.index') }}" class="btn btn-outline btn-sm shrink-0">Abrir</a>
                    </div>
                @empty
                    <p class="p-4 text-sm text-slate-500">Nenhum recrutamento.</p>
                @endforelse
            </section>
        @endif
    </div>
</x-crm-layout>
