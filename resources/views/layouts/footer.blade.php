<footer class="bg-slate-900 mt-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-10">
        <div>
            <x-logo variant="white" class="h-8 w-auto" />
            <p class="text-sm text-slate-400 mt-3 max-w-xs">A plataforma Nº1 de Remodelações e Serviços em Portugal.</p>
        </div>

        <div>
            <h3 class="font-semibold text-sm text-white mb-3">Explorar</h3>
            <ul class="space-y-2 text-sm text-slate-400">
                <li><a href="{{ route('anuncios.index') }}" class="hover:text-green-400">Anúncios</a></li>
                <li><a href="{{ route('orcamentos.index') }}" class="hover:text-green-400">Orçamentos</a></li>
                <li><a href="{{ route('recrutamentos.index') }}" class="hover:text-green-400">Recrutamento</a></li>
                <li><a href="{{ route('blog.index') }}" class="hover:text-green-400">Blog</a></li>
            </ul>
        </div>

        <div>
            <h3 class="font-semibold text-sm text-white mb-3">Conta</h3>
            <ul class="space-y-2 text-sm text-slate-400">
                @auth
                    <li><a href="{{ route('anuncios.create') }}" class="hover:text-green-400">Publicar anúncio</a></li>
                    <li><a href="{{ route('anuncios.meus') }}" class="hover:text-green-400">Os meus anúncios</a></li>
                    <li><a href="{{ route('profile.edit') }}" class="hover:text-green-400">Perfil</a></li>
                @else
                    <li><a href="{{ route('login') }}" class="hover:text-green-400">Entrar</a></li>
                    <li><a href="{{ route('anuncios.create') }}" class="hover:text-green-400">Publicar anúncio</a></li>
                @endauth
            </ul>
        </div>

        <div>
            <h3 class="font-semibold text-sm text-white mb-3">Newsletter</h3>
            <p class="text-sm text-slate-400 mb-3">Novidades e dicas, sem spam.</p>
            <x-newsletter-form />
        </div>
    </div>

    <div class="border-t border-slate-800 py-4 flex flex-col sm:flex-row items-center justify-center gap-2 sm:gap-4">
        <p class="text-center text-xs text-slate-500">&copy; {{ now()->year }} TopCasa. Todos os direitos reservados.</p>
        <a href="{{ route('termos') }}" class="text-xs text-slate-500 hover:text-green-400 underline">Termos e Condições</a>
        <a href="{{ route('politica-privacidade') }}" class="text-xs text-slate-500 hover:text-green-400 underline">Política de Privacidade</a>
        <a href="{{ route('politica-cookies') }}" class="text-xs text-slate-500 hover:text-green-400 underline">Política de Cookies</a>
    </div>
</footer>
