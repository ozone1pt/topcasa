<x-crm-layout title="Clientes">
    <div class="space-y-4">
        @if (session('status'))
            <div class="bg-green-100 text-green-800 rounded-md p-3 text-sm">{{ session('status') }}</div>
        @endif

        <form method="GET" class="flex flex-wrap gap-3 bg-white p-4 rounded-lg shadow">
            <input type="text" name="busca" value="{{ request('busca') }}" placeholder="Pesquisar..." class="rounded-md border-slate-300" />
            <select name="status" class="rounded-md border-slate-300">
                <option value="">Todos os estados</option>
                @foreach (['pendente', 'aprovado', 'rejeitado'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
            <x-primary-button>Filtrar</x-primary-button>
        </form>

        <div class="bg-white rounded-lg shadow divide-y">
            @forelse ($users as $user)
                <div class="p-4 flex items-center justify-between gap-4">
                    <div class="min-w-0">
                        <a href="{{ route('crm.users.show', $user) }}" class="font-medium text-slate-900 hover:underline">{{ $user->nomeCompleto() }} ({{ $user->username }})</a>
                        <p class="text-xs text-slate-500">{{ $user->email }} · {{ $user->tipo_conta ?? $user->user_type }} · {{ $user->anuncios_count }} anúncio(s) · registado em {{ $user->created_at?->format('d/m/Y H:i') }}</p>
                        <span @class([
                            'inline-block mt-1 text-xs font-semibold px-2 py-0.5 rounded-full',
                            'bg-green-100 text-green-800' => $user->status === 'aprovado',
                            'bg-amber-100 text-amber-800' => $user->status === 'pendente',
                            'bg-red-100 text-red-800' => $user->status === 'rejeitado',
                        ])>{{ ucfirst($user->status) }}</span>
                    </div>
                    <div class="flex gap-2 shrink-0">
                        <a href="{{ route('crm.users.show', $user) }}" class="btn btn-outline btn-sm">Ver ficha</a>
                        @if ($user->status === 'pendente')
                            <form method="POST" action="{{ route('crm.users.aprovar', $user) }}">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-sm">Aprovar</button>
                            </form>
                            <form method="POST" action="{{ route('crm.users.rejeitar', $user) }}">
                                @csrf
                                <button type="submit" class="btn btn-danger btn-sm">Rejeitar</button>
                            </form>
                        @elseif ($user->status === 'aprovado')
                            <form method="POST" action="{{ route('crm.users.rejeitar', $user) }}">
                                @csrf
                                <button type="submit" class="btn btn-outline btn-sm">Desativar</button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('crm.users.aprovar', $user) }}">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-sm">Ativar</button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <p class="p-4 text-slate-500">Sem clientes.</p>
            @endforelse
        </div>

        {{ $users->links() }}
    </div>
</x-crm-layout>
