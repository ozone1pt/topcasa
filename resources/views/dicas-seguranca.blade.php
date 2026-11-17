<x-app-layout title="Dicas de Segurança" description="Dicas para contratar com mais confiança: recomendações para ajudar a contratar profissionais e empresas com mais segurança.">
    <section class="bg-slate-900">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16 text-center">
            <h1 class="font-display font-extrabold text-4xl sm:text-5xl text-white leading-tight tracking-tight">
                Dicas para contratar com mais <span class="text-green-400">confiança</span>
            </h1>
            <p class="mt-4 text-lg text-slate-300 max-w-2xl mx-auto">
                Antes de contratar um profissional ou empresa para realizar trabalhos em sua casa, é importante tomar algumas precauções simples que podem evitar atrasos, custos inesperados e experiências desagradáveis.
            </p>
        </div>
    </section>

    <section class="py-16">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="card p-6 sm:p-8">
                <p class="text-slate-500 dark:text-slate-400">Seguem algumas recomendações para ajudar a contratar com mais segurança e confiança.</p>

                <ul class="mt-6 divide-y divide-slate-200 dark:divide-slate-700">
                    @foreach ([
                        ['icon' => 'fa-id-card', 'title' => 'Verifique o NIF e a atividade da empresa', 'text' => 'Peça sempre o NIF da empresa ou do profissional e confirme se possui atividade registada na área dos serviços que pretende contratar. Esta verificação ajuda a garantir que está a lidar com uma entidade legalmente estabelecida.'],
                        ['icon' => 'fa-user-group', 'title' => 'Peça referências de clientes anteriores', 'text' => 'Solicite contactos ou exemplos de trabalhos realizados anteriormente. Falar com clientes anteriores permite conhecer melhor a qualidade do serviço, o cumprimento de prazos e o profissionalismo da empresa.'],
                        ['icon' => 'fa-sack-dollar', 'title' => 'Evite pagamentos de adjudicação', 'text' => 'Salvo em situações em que conhece pessoalmente o profissional ou existe uma relação de confiança comprovada, evite pagar valores de adjudicação antes do início dos trabalhos. Empresas com atividade consistente e carteira de clientes normalmente não necessitam deste tipo de pagamento para garantir a execução do serviço.'],
                        ['icon' => 'fa-file-lines', 'title' => 'Solicite um orçamento detalhado por escrito', 'text' => 'O orçamento deve indicar claramente os trabalhos a realizar, materiais incluídos, prazos previstos e valores a cobrar. Quanto mais detalhado for o documento, menor será o risco de mal-entendidos futuros.'],
                        ['icon' => 'fa-scale-balanced', 'title' => 'Compare várias propostas', 'text' => 'Sempre que possível, solicite mais do que um orçamento. Comparar diferentes propostas ajuda a perceber os preços praticados no mercado e a identificar eventuais valores anormalmente baixos ou elevados.'],
                        ['icon' => 'fa-triangle-exclamation', 'title' => 'Desconfie de preços demasiado baixos', 'text' => 'Uma proposta muito abaixo da média pode parecer apelativa, mas muitas vezes resulta em materiais de menor qualidade, trabalhos incompletos ou pedidos de valores adicionais durante a execução da obra.'],
                        ['icon' => 'fa-receipt', 'title' => 'Exija fatura ou recibo', 'text' => 'A emissão de fatura demonstra profissionalismo e oferece maior proteção ao cliente em caso de litígios ou necessidade de acionar garantias.'],
                        ['icon' => 'fa-pen-to-square', 'title' => 'Formalize os acordos por escrito', 'text' => 'Mesmo em trabalhos de menor dimensão, é aconselhável que todas as condições acordadas fiquem registadas por escrito, incluindo preço, prazo e âmbito dos serviços contratados.'],
                        ['icon' => 'fa-lock', 'title' => 'Nunca efetue pagamentos integrais antecipadamente', 'text' => 'Os pagamentos devem acompanhar a evolução dos trabalhos. Evite pagar a totalidade do valor antes da conclusão do serviço.'],
                    ] as $dica)
                        <li class="py-5 flex gap-4">
                            <span class="icon-circle shrink-0"><i class="fa-solid {{ $dica['icon'] }}"></i></span>
                            <div>
                                <h3 class="font-semibold text-slate-900 dark:text-white">{{ $dica['title'] }}</h3>
                                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ $dica['text'] }}</p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>

            <p class="text-center text-slate-500 dark:text-slate-400 mt-8">
                Seguindo estas recomendações simples, poderá reduzir significativamente os riscos e contratar com maior segurança e confiança.
            </p>

            <div class="text-center mt-8">
                <a href="{{ route('anuncios.index') }}" class="btn btn-primary">Procurar Profissionais</a>
            </div>
        </div>
    </section>
</x-app-layout>
