import 'package:material_ui/material_ui.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:topcasa/core/theme.dart';
import 'package:topcasa/providers/providers.dart';
import 'package:topcasa/models/social.dart';
import 'package:topcasa/screens/alertas_screen.dart';
import 'package:topcasa/screens/anuncio_detail_screen.dart';
import 'package:topcasa/screens/anuncio_form_screen.dart';
import 'package:topcasa/screens/blog_screen.dart';
import 'package:topcasa/screens/conta_screen.dart';
import 'package:topcasa/screens/favoritos_screen.dart';
import 'package:topcasa/screens/forgot_password_screen.dart';
import 'package:topcasa/screens/home_screen.dart';
import 'package:topcasa/screens/login_screen.dart';
import 'package:topcasa/screens/mensagens_screen.dart';
import 'package:topcasa/screens/meus_anuncios_screen.dart';
import 'package:topcasa/screens/orcamentos_screen.dart';
import 'package:topcasa/screens/profile_screen.dart';
import 'package:topcasa/screens/recrutamento_screen.dart';
import 'package:topcasa/screens/register_screen.dart';
import 'package:topcasa/screens/splash_screen.dart';
import 'package:topcasa/widgets/app_shell.dart';

class TopCasaApp extends ConsumerWidget {
  const TopCasaApp({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final router = ref.watch(routerProvider);
    return MaterialApp.router(
      title: 'TopCasa',
      debugShowCheckedModeBanner: false,
      theme: temaTopCasa(),
      routerConfig: router,
    );
  }
}

final routerProvider = Provider<GoRouter>((ref) {
  final refresco = _RefrescoRouter(ref);
  ref.onDispose(refresco.dispose);

  return GoRouter(
    initialLocation: '/splash',
    refreshListenable: refresco,
    redirect: (context, state) {
      final auth = ref.read(authControllerProvider);
      final local = state.matchedLocation;
      const publicas = {'/login', '/registar', '/recuperar'};

      if (!auth.pronto) {
        return local == '/splash' ? null : '/splash';
      }
      if (local == '/splash') {
        return '/';
      }
      const reservadas = {
        '/perfil',
        '/meus-anuncios',
        '/favoritos',
        '/mensagens',
        '/alertas',
        '/anuncios/novo',
        '/orcamentos/novo',
        '/recrutamento/novo',
        '/meus-orcamentos',
        '/meu-recrutamento',
        '/reviews',
        '/password',
        '/apagar-conta',
      };
      final reservada = reservadas.contains(local) || local.endsWith('/editar') || local.startsWith('/mensagens/');
      if (reservada && !auth.autenticado) {
        return '/login';
      }
      if (auth.autenticado && publicas.contains(local)) {
        return '/';
      }
      return null;
    },
    routes: [
      GoRoute(path: '/splash', builder: (_, _) => const SplashScreen()),
      GoRoute(path: '/login', builder: (_, _) => const LoginScreen()),
      GoRoute(path: '/registar', builder: (_, _) => const RegisterScreen()),
      GoRoute(path: '/recuperar', builder: (_, _) => const ForgotPasswordScreen()),
      ShellRoute(
        builder: (_, _, child) => AppShell(child: child),
        routes: [
          GoRoute(path: '/', builder: (_, _) => const HomeScreen()),
          GoRoute(path: '/anuncios/novo', builder: (_, _) => const AnuncioFormScreen()),
          GoRoute(path: '/meus-anuncios', builder: (_, _) => const MeusAnunciosScreen()),
          GoRoute(path: '/favoritos', builder: (_, _) => const FavoritosScreen()),
          GoRoute(path: '/mensagens', builder: (_, _) => const MensagensScreen()),
          GoRoute(
            path: '/mensagens/:id',
            builder: (_, state) => MensagemScreen(id: int.tryParse(state.pathParameters['id'] ?? '') ?? 0),
          ),
          GoRoute(path: '/alertas', builder: (_, _) => const AlertasScreen()),
          GoRoute(
            path: '/anuncios/:id',
            builder: (_, state) {
              final id = int.tryParse(state.pathParameters['id'] ?? '') ?? 0;
              return AnuncioDetailScreen(id: id);
            },
            routes: [
              GoRoute(
                path: 'editar',
                builder: (_, state) => AnuncioFormScreen(anuncioId: int.tryParse(state.pathParameters['id'] ?? '')),
              ),
            ],
          ),
          GoRoute(path: '/perfil', builder: (_, _) => const ProfileScreen()),
          GoRoute(path: '/conta', builder: (_, _) => const ContaScreen()),
          GoRoute(path: '/orcamentos', builder: (_, _) => const OrcamentosScreen()),
          GoRoute(path: '/orcamentos/novo', builder: (_, _) => const OrcamentoFormScreen()),
          GoRoute(path: '/meus-orcamentos', builder: (_, _) => const OrcamentosScreen(meus: true)),
          GoRoute(
            path: '/orcamentos/:id',
            builder: (_, state) {
              final extra = state.extra;
              if (extra is! PedidoResumo) return const _Indisponivel();
              return OrcamentoDetalheScreen(pedido: extra);
            },
          ),
          GoRoute(path: '/recrutamento', builder: (_, _) => const RecrutamentoScreen()),
          GoRoute(path: '/recrutamento/novo', builder: (_, _) => const RecrutamentoFormScreen()),
          GoRoute(path: '/meu-recrutamento', builder: (_, _) => const RecrutamentoScreen(meus: true)),
          GoRoute(
            path: '/recrutamento/:id',
            builder: (_, state) {
              final extra = state.extra;
              if (extra is! PedidoResumo) return const _Indisponivel();
              return RecrutamentoDetalheScreen(pedido: extra);
            },
          ),
          GoRoute(path: '/reviews', builder: (_, _) => const ReviewsScreen()),
          GoRoute(path: '/password', builder: (_, _) => const PasswordScreen()),
          GoRoute(path: '/apagar-conta', builder: (_, _) => const ApagarContaScreen()),
          GoRoute(path: '/blog', builder: (_, _) => const BlogScreen()),
          GoRoute(
            path: '/blog/:slug',
            builder: (_, state) => ArtigoScreen(slug: state.pathParameters['slug'] ?? ''),
          ),
          GoRoute(path: '/newsletter', builder: (_, _) => const NewsletterScreen()),
        ],
      ),
    ],
  );
});

class _Indisponivel extends StatelessWidget {
  const _Indisponivel();

  @override
  Widget build(BuildContext context) {
    return const Scaffold(body: Center(child: Text('Pedido indisponível.')));
  }
}

class _RefrescoRouter extends ChangeNotifier {
  _RefrescoRouter(this._ref) {
    _ref.listen<EstadoAuth>(authControllerProvider, (_, _) => notifyListeners());
  }

  final Ref _ref;
}
