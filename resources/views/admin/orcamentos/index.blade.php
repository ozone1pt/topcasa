<x-crm-layout title="Orçamentos">
    <div class="space-y-4" x-data="{ open: null }">
        @if (session('status'))
            <div class="bg-green-100 text-green-800 rounded-md p-3 text-sm">{{ session('status') }}</div>
        @endif

        <form method="GET" class="flex flex-wrap gap-3 bg-white p-4 rounded-lg shadow">
            <input type="text" name="busca" value="{{ request('busca') }}" placeholder="Pesquisar nome, título ou telefone..." class="rounded-md border-slate-300" />
            <select name="status" class="rounded-md border-slate-300">
                <option value="">Todos os estados</option>
                @foreach (['pendente' => 'Pendente', 'validado' => 'Validado', 'rejeitado' => 'Rejeitado', 'concluido' => 'Concluído'] as $valor => $rotulo)
                    <option value="{{ $valor }}" @selected(request('status') === $valor)>{{ $rotulo }}</option>
                @endforeach
            </select>
            <x-primary-button>Filtrar</x-primary-button>
        </form>

        <div class="bg-white rounded-lg shadow divide-y">
            @forelse ($pedidos as $pedido)
                <div class="p-4">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div class="min-w-0">
                            @if ($pedido->foto)
                                @include('admin.partials.pedido-foto', ['url' => $pedido->fotoUrl()])
                            @endif
                            <p class="font-medium text-slate-900">{{ $pedido->titulo ?: 'Pedido de orçamento' }}</p>
                            <p class="text-xs text-slate-500">{{ $pedido->nome }} · {{ $pedido->telefone }}@if ($pedido->email) · {{ $pedido->email }}@endif · {{ $pedido->distrito?->nome }} · submetido em {{ $pedido->created_at?->timezone('Europe/Lisbon')->format('d/m/Y H:i') }}</p>
                            <p class="text-sm text-slate-600 mt-1 whitespace-pre-line">{{ Str::limit($pedido->descricao, 400) }}</p>
                            <p class="text-xs text-slate-400 mt-0.5"><i class="fa-solid fa-eye"></i> {{ $pedido->contacto_clicks }} clique(s) em "Ver contacto"</p>
                            @if ($pedido->status === 'rejeitado' && $pedido->motivo_rejeicao)
                                <p class="text-xs text-red-600 mt-1">Motivo da rejeição: {{ $pedido->motivo_rejeicao }}</p>
                            @endif
                        </div>
                        <div class="flex flex-wrap items-center gap-2 shrink-0">
                            <span @class([
                                'text-xs font-semibold px-2 py-0.5 rounded-full',
                                'bg-green-100 text-green-800' => $pedido->status === 'validado',
                                'bg-amber-100 text-amber-800' => $pedido->status === 'pendente',
                                'bg-red-100 text-red-800' => $pedido->status === 'rejeitado',
                                'bg-slate-100 text-slate-600' => $pedido->status === 'concluido',
                            ])>{{ ['pendente' => 'Pendente', 'validado' => 'Validado', 'rejeitado' => 'Rejeitado', 'concluido' => 'Concluído'][$pedido->status] ?? ucfirst($pedido->status) }}</span>

                            @if (in_array($pedido->status, ['pendente', 'rejeitado'], true))
                                <form method="POST" action="{{ route('crm.orcamentos.aprovar', $pedido) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-primary btn-sm">Aprovar</button>
                                </form>
                            @endif
                            @if ($pedido->status === 'pendente')
                                <form method="POST" action="{{ route('crm.orcamentos.rejeitar', $pedido) }}" class="flex items-center gap-1">
                                    @csrf
                                    <input type="text" name="motivo_rejeicao" maxlength="500" placeholder="Motivo (opcional)" class="rounded-md border-slate-300 text-sm" style="width:11rem;padding:.25rem .5rem" />
                                    <button type="submit" class="btn btn-danger btn-sm">Rejeitar</button>
                                </form>
                            @endif

                            <button type="button" class="btn btn-outline btn-sm" @click="open = open === {{ $pedido->id }} ? null : {{ $pedido->id }}">Gerir</button>
                            <form method="POST" action="{{ route('crm.orcamentos.destroy', $pedido) }}" onsubmit="return confirm('Remover este pedido?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-sm text-red-600 underline">Remover</button>
                            </form>
                        </div>
                    </div>

                    <form
                        x-show="open === {{ $pedido->id }}"
                        x-cloak
                        method="POST"
                        action="{{ route('crm.orcamentos.update', $pedido) }}"
                        class="mt-3 pt-3 border-t space-y-3"
                    >
                        @csrf
                        @method('PUT')
                        @if ($pedido->isAprovado())
                            <div>
                                <x-input-label value="Estado" />
                                <select name="status" class="mt-1 block w-full sm:w-56 border-slate-300 rounded-md">
                                    <option value="validado" @selected($pedido->status === 'validado')>Validado</option>
                                    <option value="concluido" @selected($pedido->status === 'concluido')>Concluído</option>
                                </select>
                            </div>
                        @else
                            <p class="text-sm text-slate-500">Este pedido ainda não está aprovado. Para o publicar usa "Aprovar" (os emails só são enviados nessa altura).</p>
                        @endif
                        <div>
                            <x-input-label value="Notas internas" />
                            <textarea name="notas_admin" rows="2" class="mt-1 block w-full border-slate-300 rounded-md">{{ $pedido->notas_admin }}</textarea>
                        </div>
                        <x-primary-button>Guardar</x-primary-button>
                    </form>
                </div>
            @empty
                <p class="p-4 text-slate-500">Sem pedidos.</p>
            @endforelse
        </div>

        {{ $pedidos->links() }}
    </div>
</x-crm-layout>
