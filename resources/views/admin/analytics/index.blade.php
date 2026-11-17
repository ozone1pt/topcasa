<x-crm-layout title="Analytics de cliques">
<div class="space-y-4 space-y-4">
        <a href="{{ route('crm.analytics.exportar') }}" class="btn btn-dark btn-sm">Exportar CSV</a>

        <div class="bg-white dark:bg-slate-800 rounded-lg shadow overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left text-slate-500 border-b dark:border-slate-700">
                        <th class="p-3">Anúncio</th>
                        <th class="p-3">Views</th>
                        <th class="p-3">Tel.</th>
                        <th class="p-3">Email</th>
                        <th class="p-3">WhatsApp</th>
                        <th class="p-3">Mensagens</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($anuncios as $anuncio)
                        <tr class="border-b dark:border-slate-700">
                            <td class="p-3">{{ $anuncio->titulo }}</td>
                            <td class="p-3">{{ $anuncio->views }}</td>
                            <td class="p-3">{{ $anuncio->phone_clicks }}</td>
                            <td class="p-3">{{ $anuncio->email_clicks }}</td>
                            <td class="p-3">{{ $anuncio->whatsapp_clicks }}</td>
                            <td class="p-3">{{ $anuncio->message_count }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{ $anuncios->links() }}
    </div>
</x-crm-layout>
