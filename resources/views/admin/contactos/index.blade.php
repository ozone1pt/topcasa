<x-crm-layout title="Contactos">
    <div class="space-y-6">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="card p-5 flex items-center gap-4">
                <span class="icon-circle"><i class="fa-solid fa-phone"></i></span>
                <span>
                    <span class="block text-2xl font-bold text-slate-900">{{ number_format($porOrigemTotal['telefone'], 0, ',', '.') }}</span>
                    <span class="block text-sm text-slate-500">Contacto (telefone)</span>
                </span>
            </div>
            <div class="card p-5 flex items-center gap-4">
                <span class="icon-circle"><i class="fa-brands fa-whatsapp"></i></span>
                <span>
                    <span class="block text-2xl font-bold text-slate-900">{{ number_format($porOrigemTotal['whatsapp'], 0, ',', '.') }}</span>
                    <span class="block text-sm text-slate-500">WhatsApp</span>
                </span>
            </div>
            <div class="card p-5 flex items-center gap-4">
                <span class="icon-circle"><i class="fa-solid fa-envelope"></i></span>
                <span>
                    <span class="block text-2xl font-bold text-slate-900">{{ number_format($porOrigemTotal['site'], 0, ',', '.') }}</span>
                    <span class="block text-sm text-slate-500">Site (mensagens)</span>
                </span>
            </div>
        </div>

        <div id="emails" class="bg-white rounded-lg shadow p-6 space-y-4">
            <div class="flex items-center justify-between gap-3">
                <h2 class="font-semibold text-slate-900"><i class="fa-solid fa-envelope text-green-600 mr-1"></i> Emails enviados pelo site</h2>
            </div>

            @unless ($emails['entregaReal'])
                <div class="bg-amber-100 text-amber-800 rounded-md p-3 text-sm">
                    <strong>Atenção:</strong> o servidor de email ainda não está ligado ("{{ config('mail.default') }}"), por isso os emails abaixo ficam apenas registados e <strong>não chegam</strong> às caixas de correio. Os números contam os envios que o site tentou fazer.
                </div>
            @endunless

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="card p-4 text-center">
                    <p class="text-2xl font-bold text-slate-900">{{ number_format($emails['total'], 0, ',', '.') }}</p>
                    <p class="text-xs text-slate-500">Total enviados</p>
                </div>
                <div class="card p-4 text-center">
                    <p class="text-2xl font-bold text-slate-900">{{ number_format($emails['periodo'], 0, ',', '.') }}</p>
                    <p class="text-xs text-slate-500">Últimos {{ $dias }} dias</p>
                </div>
                <div class="card p-4 text-center">
                    <p class="text-2xl font-bold text-slate-900">{{ number_format((int) ($emails['porTipo']['contacto_revelado'] ?? 0), 0, ',', '.') }}</p>
                    <p class="text-xs text-slate-500">"Guardaram o teu contacto"</p>
                </div>
                <div class="card p-4 text-center">
                    <p class="text-2xl font-bold {{ $emails['falhados'] ? 'text-red-600' : 'text-slate-900' }}">{{ number_format($emails['falhados'], 0, ',', '.') }}</p>
                    <p class="text-xs text-slate-500">Falharam</p>
                </div>
            </div>

            @php
                $tiposEmail = [
                    'contacto_revelado' => 'Contacto guardado (dono do anúncio)',
                    'anuncio_publicado' => 'Anúncio publicado (confirmação)',
                    'orcamento_confirmacao' => 'Orçamento publicado (confirmação)',
                    'recrutamento_confirmacao' => 'Vaga publicada (confirmação)',
                    'orcamento' => 'Novo orçamento (profissionais)',
                    'recrutamento' => 'Nova vaga (profissionais)',
                ];
            @endphp

            @if ($emails['recentes']->isEmpty())
                <p class="text-sm text-slate-500">Ainda não foi enviado nenhum email.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left text-slate-500 border-b">
                                <th class="p-2">Quando</th>
                                <th class="p-2">Tipo</th>
                                <th class="p-2">Anúncio</th>
                                <th class="p-2">Destinatários</th>
                                <th class="p-2">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($emails['recentes'] as $email)
                                <tr class="border-b">
                                    <td class="p-2 whitespace-nowrap">{{ ($email->enviado_em ?? $email->created_at)?->format('d/m/Y H:i') }}</td>
                                    <td class="p-2">{{ $tiposEmail[$email->tipo] ?? $email->tipo }}</td>
                                    <td class="p-2 max-w-xs truncate">{{ $email->anuncio_id ? ($emails['titulos'][$email->anuncio_id] ?? '#'.$email->anuncio_id) : '—' }}</td>
                                    <td class="p-2">{{ $email->destinatarios_count }}</td>
                                    <td class="p-2">
                                        @if ($email->sucesso)
                                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-green-100 text-green-800">Enviado</span>
                                        @elseif ($email->enviado_em)
                                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-red-100 text-red-800" title="{{ $email->erro }}">Falhou</span>
                                        @else
                                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-slate-100 text-slate-600">Pendente</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-semibold text-slate-900">Contactos por período (dia a dia)</h2>
                <form method="GET" class="flex gap-2">
                    <select name="dias" onchange="this.form.submit()" class="text-sm rounded-md border-slate-300">
                        <option value="7" @selected($dias === 7)>Últimos 7 dias</option>
                        <option value="30" @selected($dias === 30)>Últimos 30 dias</option>
                        <option value="90" @selected($dias === 90)>Últimos 90 dias</option>
                    </select>
                </form>
            </div>

            @if ($porPeriodo->isEmpty())
                <p class="text-sm text-slate-500">Ainda sem eventos registados neste período. Este detalhe dia-a-dia começou a ser guardado agora - os cliques feitos antes disto continuam contabilizados nos totais acima, só não têm data associada.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left text-slate-500 border-b">
                                <th class="p-2">Dia</th>
                                <th class="p-2">Telefone</th>
                                <th class="p-2">WhatsApp</th>
                                <th class="p-2">Site</th>
                                <th class="p-2">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($porPeriodo as $dia => $linhas)
                                @php
                                    $porOrigem = $linhas->keyBy('origem');
                                    $totalDia = $linhas->sum('total');
                                @endphp
                                <tr class="border-b">
                                    <td class="p-2">{{ \Illuminate\Support\Carbon::parse($dia)->format('d/m/Y') }}</td>
                                    <td class="p-2">{{ $porOrigem->get('telefone')?->total ?? 0 }}</td>
                                    <td class="p-2">{{ $porOrigem->get('whatsapp')?->total ?? 0 }}</td>
                                    <td class="p-2">{{ $porOrigem->get('site')?->total ?? 0 }}</td>
                                    <td class="p-2 font-semibold">{{ $totalDia }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="font-semibold text-slate-900 mb-4">Contactos por profissional</h2>
            @if ($porProfissional->isEmpty())
                <p class="text-sm text-slate-500">Sem contactos registados ainda.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left text-slate-500 border-b">
                                <th class="p-2">Profissional</th>
                                <th class="p-2">Telefone</th>
                                <th class="p-2">WhatsApp</th>
                                <th class="p-2">Site</th>
                                <th class="p-2">Total</th>
                                <th class="p-2"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($porProfissional as $linha)
                                <tr class="border-b">
                                    <td class="p-2">{{ $linha->user->nomeCompleto() }}</td>
                                    <td class="p-2">{{ $linha->user->soma_telefone ?? 0 }}</td>
                                    <td class="p-2">{{ $linha->user->soma_whatsapp ?? 0 }}</td>
                                    <td class="p-2">{{ $linha->user->soma_site ?? 0 }}</td>
                                    <td class="p-2 font-semibold">{{ $linha->total }}</td>
                                    <td class="p-2"><a href="{{ route('crm.users.show', $linha->user) }}" class="text-green-600 hover:underline">Ver ficha</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <div class="bg-white rounded-lg shadow p-4">
            <a href="{{ route('crm.analytics.index') }}" class="text-sm text-green-600 hover:underline">Ver contactos por anúncio &rarr;</a>
        </div>
    </div>
</x-crm-layout>
