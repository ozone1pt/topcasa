<x-crm-layout :title="$user->nomeCompleto()">
    <div class="max-w-4xl space-y-6">
        <a href="{{ route('crm.users.index') }}" class="text-sm text-slate-500 hover:underline">&larr; Voltar a clientes</a>

        @if (session('status'))
            <div class="bg-green-100 text-green-800 rounded-md p-3 text-sm">{{ session('status') }}</div>
        @endif

        <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
            @foreach ([
                ['label' => 'Anúncios', 'value' => $user->anuncios->count()],
                ['label' => 'Visualizações', 'value' => $contactos['views']],
                ['label' => 'Cliques telefone', 'value' => $contactos['phone_clicks']],
                ['label' => 'Cliques WhatsApp', 'value' => $contactos['whatsapp_clicks']],
                ['label' => 'Mensagens', 'value' => $contactos['message_count']],
            ] as $card)
                <div class="card p-4 text-center">
                    <p class="text-xl font-bold text-slate-900">{{ number_format($card['value'], 0, ',', '.') }}</p>
                    <p class="text-xs text-slate-500">{{ $card['label'] }}</p>
                </div>
            @endforeach
        </div>

        <form method="POST" action="{{ route('crm.users.update', $user) }}" class="bg-white rounded-lg shadow p-6 space-y-4">
            @csrf
            @method('PUT')

            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-lg text-slate-900">Dados do cliente</h2>
                <span @class([
                    'text-xs font-semibold px-2 py-0.5 rounded-full',
                    'bg-green-100 text-green-800' => $user->status === 'aprovado',
                    'bg-amber-100 text-amber-800' => $user->status === 'pendente',
                    'bg-red-100 text-red-800' => $user->status === 'rejeitado',
                ])>{{ ucfirst($user->status) }}</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="nome" value="Nome" />
                    <x-text-input id="nome" name="nome" class="block mt-1 w-full" :value="old('nome', $user->nome)" required />
                </div>
                <div>
                    <x-input-label for="apelido" value="Apelido" />
                    <x-text-input id="apelido" name="apelido" class="block mt-1 w-full" :value="old('apelido', $user->apelido)" />
                </div>
                <div>
                    <x-input-label for="email" value="Email" />
                    <x-text-input id="email" name="email" type="email" class="block mt-1 w-full" :value="old('email', $user->email)" required />
                </div>
                <div>
                    <x-input-label for="telefone" value="Telefone" />
                    <x-text-input id="telefone" name="telefone" class="block mt-1 w-full" :value="old('telefone', $user->telefone)" />
                </div>
                <div>
                    <x-input-label for="nif" value="NIF" />
                    <x-text-input id="nif" name="nif" class="block mt-1 w-full" :value="old('nif', $user->nif)" />
                </div>
                <div>
                    <x-input-label for="tipo_conta" value="Tipo de conta" />
                    <select id="tipo_conta" name="tipo_conta" class="mt-1 block w-full border-slate-300 rounded-md">
                        <option value="">—</option>
                        @foreach (['particular', 'profissional', 'empresa'] as $tipo)
                            <option value="{{ $tipo }}" @selected(old('tipo_conta', $user->tipo_conta) === $tipo)>{{ ucfirst($tipo) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <x-input-label for="empresa_nome" value="Nome da empresa" />
                    <x-text-input id="empresa_nome" name="empresa_nome" class="block mt-1 w-full" :value="old('empresa_nome', $user->empresa_nome)" />
                </div>
            </div>

            <div class="space-y-2 border-t pt-4">
                <p class="text-sm font-medium text-slate-700">Notificações automáticas por email</p>
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="notificar_orcamentos" value="1" @checked(old('notificar_orcamentos', $user->notificar_orcamentos)) />
                    <span class="text-sm text-slate-700">Receber novos pedidos de orçamento</span>
                </label>
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="notificar_recrutamento" value="1" @checked(old('notificar_recrutamento', $user->notificar_recrutamento)) />
                    <span class="text-sm text-slate-700">Receber novas vagas de recrutamento</span>
                </label>
            </div>

            <x-primary-button>Guardar alterações</x-primary-button>
        </form>

        <div class="bg-white rounded-lg shadow divide-y">
            <div class="p-4">
                <h2 class="font-semibold text-slate-900">Anúncios associados ({{ $user->anuncios->count() }})</h2>
            </div>
            @forelse ($user->anuncios as $anuncio)
                <div class="p-4 flex flex-wrap items-center justify-between gap-3">
                    <div class="min-w-0">
                        <p class="font-medium text-slate-900">{{ $anuncio->titulo }}</p>
                        <p class="text-xs text-slate-500">{{ $anuncio->categoria?->nome }} · {{ ucfirst($anuncio->status) }} · {{ $anuncio->phone_clicks + $anuncio->whatsapp_clicks }} contacto(s)</p>
                    </div>
                    <div class="flex gap-2 shrink-0">
                        <a href="{{ route('crm.anuncios.edit', $anuncio) }}" class="btn btn-primary btn-sm">
                            <i class="fa-solid fa-pen"></i> Editar
                        </a>
                        <a href="{{ route('anuncios.show', $anuncio) }}" target="_blank" rel="noopener" class="btn btn-outline btn-sm">
                            <i class="fa-solid fa-arrow-up-right-from-square"></i> Ver anúncio
                        </a>
                    </div>
                </div>
            @empty
                <p class="p-4 text-slate-500 text-sm">Sem anúncios.</p>
            @endforelse
        </div>
    </div>
</x-crm-layout>
