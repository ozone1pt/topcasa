<x-app-layout title="Termos e Condições" description="Termos e condições de utilização da plataforma TopCasa.">
    <section class="bg-slate-900">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16 text-center">
            <h1 class="font-display font-extrabold text-4xl sm:text-5xl text-white leading-tight tracking-tight">
                Termos e <span class="text-green-400">Condições</span>
            </h1>
            <p class="mt-4 text-lg text-slate-300 max-w-2xl mx-auto">
                Última atualização: {{ now()->format('d/m/Y') }}
            </p>
        </div>
    </section>

    <section class="py-16">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="card p-6 sm:p-8 space-y-8 text-slate-700 dark:text-slate-300">
                <div>
                    <h2 class="font-semibold text-lg text-slate-900 dark:text-white mb-2">1. Objeto</h2>
                    <p>A TopCasa (adiante "plataforma", "site" ou "nós") é um serviço online que liga particulares e empresas ("clientes") a profissionais e empresas prestadoras de serviços domésticos ("anunciantes"), permitindo a publicação de anúncios de serviços, pedidos de orçamento e pedidos de recrutamento em Portugal. Ao utilizar a plataforma, aceita estes Termos e Condições na sua totalidade.</p>
                </div>

                <div>
                    <h2 class="font-semibold text-lg text-slate-900 dark:text-white mb-2">2. Natureza do serviço</h2>
                    <p>A TopCasa atua exclusivamente como intermediária, disponibilizando um espaço de publicação e contacto entre clientes e anunciantes. Não somos parte em qualquer contrato de prestação de serviços celebrado entre utilizadores, não empregamos os profissionais anunciados e não garantimos a qualidade, legalidade, pontualidade ou resultado dos serviços prestados por terceiros através da plataforma.</p>
                </div>

                <div>
                    <h2 class="font-semibold text-lg text-slate-900 dark:text-white mb-2">3. Registo e conta de utilizador</h2>
                    <p>Para publicar anúncios, pedidos de orçamento ou de recrutamento pode ser necessário criar uma conta. É responsável por manter a confidencialidade dos seus dados de acesso e por toda a atividade realizada através da sua conta. As informações fornecidas devem ser verdadeiras, exatas e mantidas atualizadas.</p>
                </div>

                <div>
                    <h2 class="font-semibold text-lg text-slate-900 dark:text-white mb-2">4. Conteúdo publicado</h2>
                    <p>Ao publicar um anúncio, pedido de orçamento, recrutamento, avaliação ou qualquer outro conteúdo, o utilizador garante que este é verdadeiro, não infringe direitos de terceiros e não viola a lei aplicável. Reservamo-nos o direito de moderar, recusar, editar ou remover qualquer conteúdo que viole estes Termos, sem aviso prévio, incluindo conteúdo enganoso, ofensivo, ilegal ou que não corresponda à finalidade da plataforma.</p>
                </div>

                <div>
                    <h2 class="font-semibold text-lg text-slate-900 dark:text-white mb-2">5. Contacto entre utilizadores</h2>
                    <p>A plataforma disponibiliza formas de contacto (mensagens, telefone, WhatsApp) entre clientes e anunciantes. A negociação, contratação, execução e pagamento de qualquer serviço são da exclusiva responsabilidade dos utilizadores envolvidos. Recomendamos a leitura das nossas <a href="{{ route('dicas-seguranca') }}" class="text-green-600 dark:text-green-400 underline">dicas de segurança</a> antes de contratar ou aceitar um serviço.</p>
                </div>

                <div>
                    <h2 class="font-semibold text-lg text-slate-900 dark:text-white mb-2">6. Limitação de responsabilidade</h2>
                    <p>Na máxima medida permitida por lei, a TopCasa não é responsável por danos diretos, indiretos, materiais ou morais resultantes de: (i) serviços prestados ou contratados através da plataforma; (ii) conduta de qualquer utilizador; (iii) inexatidão de informação publicada por terceiros; (iv) indisponibilidade temporária ou interrupção do serviço.</p>
                </div>

                <div>
                    <h2 class="font-semibold text-lg text-slate-900 dark:text-white mb-2">7. Dados pessoais</h2>
                    <p>O tratamento de dados pessoais na plataforma é feito em conformidade com o Regulamento Geral de Proteção de Dados (RGPD). Os dados fornecidos são utilizados apenas para os fins de funcionamento da plataforma, nomeadamente a criação de conta, publicação de anúncios e intermediação de contactos entre utilizadores.</p>
                </div>

                <div>
                    <h2 class="font-semibold text-lg text-slate-900 dark:text-white mb-2">8. Alterações aos termos</h2>
                    <p>Podemos alterar estes Termos e Condições a qualquer momento, publicando a versão atualizada nesta página. A utilização continuada da plataforma após a publicação de alterações implica a aceitação das mesmas.</p>
                </div>

                <div>
                    <h2 class="font-semibold text-lg text-slate-900 dark:text-white mb-2">9. Lei aplicável</h2>
                    <p>Estes Termos e Condições regem-se pela lei portuguesa. Qualquer litígio emergente da utilização da plataforma será submetido aos tribunais portugueses competentes.</p>
                </div>

                <div>
                    <h2 class="font-semibold text-lg text-slate-900 dark:text-white mb-2">10. Contacto</h2>
                    <p>Para qualquer questão relacionada com estes Termos e Condições, pode contactar-nos através da página de <a href="{{ route('anuncios.index') }}" class="text-green-600 dark:text-green-400 underline">contactos</a> da plataforma.</p>
                </div>
            </div>
        </div>
    </section>
</x-app-layout>
