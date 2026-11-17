import 'package:material_ui/material_ui.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:topcasa/core/theme.dart';
import 'package:topcasa/providers/providers.dart';
import 'package:topcasa/screens/anuncio_detail_screen.dart';
import 'package:topcasa/screens/forgot_password_screen.dart';
import 'package:topcasa/screens/home_screen.dart';
import 'package:topcasa/screens/login_screen.dart';
import 'package:topcasa/screens/profile_screen.dart';
import 'package:topcasa/screens/register_screen.dart';
import 'package:topcasa/screens/splash_screen.dart';

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
      if (local == '/perfil' && !auth.autenticado) {
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
      GoRoute(path: '/', builder: (_, _) => const HomeScreen()),
      GoRoute(
        path: '/anuncios/:id',
        builder: (_, state) {
          final id = int.tryParse(state.pathParameters['id'] ?? '') ?? 0;
          return AnuncioDetailScreen(id: id);
        },
      ),
      GoRoute(path: '/perfil', builder: (_, _) => const ProfileScreen()),
    ],
  );
});

class _RefrescoRouter extends ChangeNotifier {
  _RefrescoRouter(this._ref) {
    _ref.listen<EstadoAuth>(authControllerProvider, (_, _) => notifyListeners());
  }

  final Ref _ref;
}
