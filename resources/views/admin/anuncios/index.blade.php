<x-crm-layout title="Anúncios">
    <div class="space-y-4">
        @if (session('status'))
            <div class="bg-green-100 text-green-800 rounded-md p-3 text-sm">{{ session('status') }}</div>
        @endif

        <form method="GET" class="flex flex-wrap gap-3 bg-white p-4 rounded-lg shadow">
            <input type="text" name="busca" value="{{ request('busca') }}" placeholder="Pesquisar título ou empresa..." class="rounded-md border-slate-300" />

            <select name="status" class="rounded-md border-slate-300">
                <option value="">Todos os estados</option>
                @foreach (['rascunho', 'pendente', 'aprovado', 'rejeitado', 'expirado'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>

            <select name="categoria_id" class="rounded-md border-slate-300">
                <option value="">Todas as categorias</option>
                @foreach ($categorias as $categoria)
                    <option value="{{ $categoria->id }}" @selected(request('categoria_id') == $categoria->id)>{{ $categoria->nome }}</option>
                @endforeach
            </select>

            <x-primary-button>Filtrar</x-primary-button>
        </form>

        <div class="bg-white rounded-lg shadow divide-y">
            @forelse ($anuncios as $anuncio)
                <div class="p-4 flex flex-wrap items-center justify-between gap-3">
                    <div class="min-w-0">
                        @if ($anuncio->galeria->isNotEmpty())
                            <div class="mb-3">
                                <x-galeria-editavel :anuncio="$anuncio" :remover="false" tamanho="w-20 h-20" />
                            </div>
                        @endif
                        <a href="{{ route('anuncios.show', $anuncio) }}" target="_blank" class="font-medium text-slate-900 hover:underline">{{ $anuncio->titulo }}</a>
                        <p class="text-sm text-slate-500">
                            {{ $anuncio->categoria?->nome }} · {{ $anuncio->distrito?->nome }} · por {{ $anuncio->user?->nomeCompleto() }}
                        </p>
                        <p class="text-xs text-slate-500 mt-1 flex flex-wrap gap-x-4 gap-y-0.5">
                            <span title="Visualizações do anúncio"><i class="fa-solid fa-eye w-4 text-slate-400"></i> {{ number_format($anuncio->views, 0, ',', '.') }} visualizações</span>
                            <span title="Cliques em Ver contacto + WhatsApp"><i class="fa-solid fa-phone w-4 text-slate-400"></i> {{ number_format($anuncio->phone_clicks + $anuncio->whatsapp_clicks, 0, ',', '.') }} cliques nos contactos ({{ $anuncio->phone_clicks }} telefone · {{ $anuncio->whatsapp_clicks }} WhatsApp)</span>
                            <span title="Mensagens recebidas pelo site"><i class="fa-solid fa-envelope w-4 text-slate-400"></i> {{ number_format($anuncio->message_count, 0, ',', '.') }} mensagens</span>
                        </p>
                        <span @class([
                            'inline-block mt-1 text-xs font-semibold px-2 py-0.5 rounded-full',
                            'bg-green-100 text-green-800' => $anuncio->status === 'aprovado',
                            'bg-amber-100 text-amber-800' => $anuncio->status === 'pendente',
                            'bg-slate-100 text-slate-600' => $anuncio->status === 'rascunho',
                            'bg-red-100 text-red-800' => in_array($anuncio->status, ['rejeitado', 'expirado']),
                        ])>{{ ucfirst($anuncio->status) }}</span>
                    </div>
                    <div class="flex flex-wrap gap-2 shrink-0">
                        @if ($anuncio->status === 'pendente')
                            <form method="POST" action="{{ route('crm.anuncios.aprovar', $anuncio) }}">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-sm">Aprovar</button>
                            </form>
                            <form method="POST" action="{{ route('crm.anuncios.rejeitar', $anuncio) }}">
                                @csrf
                                <button type="submit" class="btn btn-danger btn-sm">Rejeitar</button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('crm.anuncios.toggle', $anuncio) }}">
                                @csrf
                                <button type="submit" class="btn btn-sm {{ $anuncio->isAprovado() ? 'btn-outline' : 'btn-primary' }}">
                                    {{ $anuncio->isAprovado() ? 'Desativar' : 'Ativar' }}
                                </button>
                            </form>
                        @endif

                        <a href="{{ route('crm.anuncios.edit', $anuncio) }}" class="btn btn-outline btn-sm">Editar</a>

                        <form method="POST" action="{{ route('crm.anuncios.duplicar', $anuncio) }}">
                            @csrf
                            <button type="submit" class="btn btn-outline btn-sm">Duplicar</button>
                        </form>

                        <form method="POST" action="{{ route('crm.anuncios.destroy', $anuncio) }}" onsubmit="return confirm('Eliminar este anúncio definitivamente?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-sm text-red-600 underline">Eliminar</button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="p-4 text-slate-500">Nenhum anúncio encontrado.</p>
            @endforelse
        </div>

        {{ $anuncios->links() }}
    </div>
</x-crm-layout>
