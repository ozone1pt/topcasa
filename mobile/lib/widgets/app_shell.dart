import 'package:go_router/go_router.dart';
import 'package:material_ui/material_ui.dart';

class AppShell extends StatelessWidget {
  const AppShell({super.key, required this.child});

  final Widget child;

  int _indice(String caminho) {
    if (caminho.startsWith('/orcamentos')) return 1;
    if (caminho.startsWith('/recrutamento')) return 2;
    if (caminho.startsWith('/mensagens')) return 3;
    if (caminho.startsWith('/conta') ||
        caminho.startsWith('/perfil') ||
        caminho.startsWith('/alertas') ||
        caminho.startsWith('/blog') ||
        caminho.startsWith('/favoritos') ||
        caminho.startsWith('/meus') ||
        caminho.startsWith('/reviews')) {
      return 4;
    }
    return 0;
  }

  @override
  Widget build(BuildContext context) {
    final caminho = GoRouterState.of(context).uri.path;
    const destinos = ['/', '/orcamentos', '/recrutamento', '/mensagens', '/conta'];

    return Scaffold(
      body: child,
      bottomNavigationBar: NavigationBar(
        selectedIndex: _indice(caminho),
        onDestinationSelected: (indice) => context.go(destinos[indice]),
        destinations: const [
          NavigationDestination(icon: Icon(Icons.home_outlined), selectedIcon: Icon(Icons.home), label: 'Início'),
          NavigationDestination(icon: Icon(Icons.request_quote_outlined), label: 'Orçamentos'),
          NavigationDestination(icon: Icon(Icons.engineering_outlined), label: 'Recrutamento'),
          NavigationDestination(icon: Icon(Icons.mail_outline), label: 'Mensagens'),
          NavigationDestination(icon: Icon(Icons.person_outline), label: 'Conta'),
        ],
      ),
    );
  }
}
