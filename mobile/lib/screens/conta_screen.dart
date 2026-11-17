import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:material_ui/material_ui.dart';
import 'package:topcasa/core/api_exception.dart';
import 'package:topcasa/core/config.dart';
import 'package:topcasa/core/theme.dart';
import 'package:topcasa/models/social.dart';
import 'package:topcasa/providers/providers.dart';
import 'package:url_launcher/url_launcher.dart';

class ContaScreen extends ConsumerStatefulWidget {
  const ContaScreen({super.key});

  @override
  ConsumerState<ContaScreen> createState() => _ContaScreenState();
}

class _ContaScreenState extends ConsumerState<ContaScreen> {
  ResumoConta? _resumo;
  String? _erro;

  @override
  void initState() {
    super.initState();
    _carregar();
  }

  Future<void> _carregar() async {
    if (!ref.read(authControllerProvider).autenticado) return;
    try {
      final resumo = await ref.read(apiClientProvider).resumo();
      if (mounted) setState(() => _resumo = resumo);
    } catch (erro) {
      if (mounted) setState(() => _erro = mensagemErro(erro));
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = ref.watch(authControllerProvider);
    if (!auth.autenticado) {
      return Scaffold(
        appBar: AppBar(title: const Text('Conta')),
        body: Center(
          child: FilledButton(onPressed: () => context.push('/login'), child: const Text('Entrar')),
        ),
      );
    }
    final resumo = _resumo;
    return Scaffold(
      appBar: AppBar(title: const Text('Conta')),
      body: ListView(
        children: [
          ListTile(title: Text(auth.utilizador?.nomeApresentacao ?? ''), subtitle: Text(auth.utilizador?.email ?? '')),
          if (resumo != null)
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16),
              child: Text(
                'Anúncios ${resumo.anuncios['total'] ?? 0} · por aprovar ${resumo.anuncios['pendente'] ?? 0} · '
                'mensagens ${resumo.mensagensNaoLidas} · alertas ${resumo.alertasNaoLidos} · '
                'favoritos ${resumo.favoritos} · avaliações por moderar ${resumo.reviewsPorModerar}',
              ),
            ),
          if (_erro != null) ListTile(title: Text(_erro!)),
          ListTile(title: const Text('Perfil'), onTap: () => context.push('/perfil')),
          ListTile(title: const Text('Os meus anúncios'), onTap: () => context.push('/meus-anuncios')),
          ListTile(title: const Text('Favoritos'), onTap: () => context.push('/favoritos')),
          ListTile(title: const Text('Os meus orçamentos'), onTap: () => context.push('/meus-orcamentos')),
          ListTile(title: const Text('O meu recrutamento'), onTap: () => context.push('/meu-recrutamento')),
          ListTile(title: const Text('Avaliações recebidas'), onTap: () => context.push('/reviews')),
          ListTile(title: const Text('Alertas'), onTap: () => context.push('/alertas')),
          ListTile(
            title: const Text('Marcar alertas como lidos'),
            onTap: () async {
              await ref.read(apiClientProvider).marcarAlertasLidos();
              await _carregar();
            },
          ),
          ListTile(title: const Text('Palavra-passe'), onTap: () => context.push('/password')),
          ListTile(title: const Text('Blog'), onTap: () => context.push('/blog')),
          ListTile(title: const Text('Newsletter'), onTap: () => context.push('/newsletter')),
          ListTile(title: const Text('Termos e condições'), onTap: () => _abrir('/termos-e-condicoes')),
          ListTile(title: const Text('Política de privacidade'), onTap: () => _abrir('/politica-de-privacidade')),
          ListTile(title: const Text('Política de cookies'), onTap: () => _abrir('/politica-de-cookies')),
          ListTile(title: const Text('Dicas de segurança'), onTap: () => _abrir('/dicas-de-seguranca')),
          ListTile(
            title: const Text('Terminar sessão'),
            onTap: () async {
              await ref.read(authControllerProvider.notifier).terminarSessao();
              if (context.mounted) context.go('/');
            },
          ),
          ListTile(
            title: const Text('Apagar conta', style: TextStyle(color: Colors.red)),
            onTap: () => context.push('/apagar-conta'),
          ),
        ],
      ),
    );
  }

  Future<void> _abrir(String caminho) => launchUrl(Uri.parse('${AppConfig.siteUrl}$caminho'), mode: LaunchMode.externalApplication);
}

class PasswordScreen extends ConsumerStatefulWidget {
  const PasswordScreen({super.key});

  @override
  ConsumerState<PasswordScreen> createState() => _PasswordScreenState();
}

class _PasswordScreenState extends ConsumerState<PasswordScreen> {
  final _atual = TextEditingController();
  final _nova = TextEditingController();
  String? _erro;

  @override
  void dispose() {
    _atual.dispose();
    _nova.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Palavra-passe')),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          TextField(controller: _atual, obscureText: true, decoration: const InputDecoration(labelText: 'Palavra-passe atual')),
          const SizedBox(height: 12),
          TextField(controller: _nova, obscureText: true, decoration: const InputDecoration(labelText: 'Nova palavra-passe')),
          if (_erro != null) Text(_erro!, style: TextStyle(color: Theme.of(context).colorScheme.error)),
          const SizedBox(height: 12),
          FilledButton(
            onPressed: () async {
              try {
                await ref.read(apiClientProvider).alterarPassword(_atual.text, _nova.text);
                if (context.mounted) {
                  ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Palavra-passe atualizada.')));
                  context.pop();
                }
              } catch (erro) {
                setState(() => _erro = mensagemErro(erro));
              }
            },
            child: const Text('Guardar'),
          ),
        ],
      ),
    );
  }
}

class ApagarContaScreen extends ConsumerStatefulWidget {
  const ApagarContaScreen({super.key});

  @override
  ConsumerState<ApagarContaScreen> createState() => _ApagarContaScreenState();
}

class _ApagarContaScreenState extends ConsumerState<ApagarContaScreen> {
  final _password = TextEditingController();
  String? _erro;

  @override
  void dispose() {
    _password.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Apagar conta')),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          const Text('Esta ação é definitiva, como no site. Confirma com a palavra-passe.'),
          const SizedBox(height: 12),
          TextField(controller: _password, obscureText: true, decoration: const InputDecoration(labelText: 'Palavra-passe')),
          if (_erro != null) Text(_erro!, style: TextStyle(color: Theme.of(context).colorScheme.error)),
          const SizedBox(height: 12),
          FilledButton(
            style: FilledButton.styleFrom(backgroundColor: Colors.red),
            onPressed: () async {
              try {
                await ref.read(apiClientProvider).apagarConta(_password.text);
                await ref.read(authControllerProvider.notifier).terminarSessao();
                if (context.mounted) context.go('/');
              } catch (erro) {
                setState(() => _erro = mensagemErro(erro));
              }
            },
            child: const Text('Apagar a minha conta'),
          ),
        ],
      ),
    );
  }
}

class ReviewsScreen extends ConsumerStatefulWidget {
  const ReviewsScreen({super.key});

  @override
  ConsumerState<ReviewsScreen> createState() => _ReviewsScreenState();
}

class _ReviewsScreenState extends ConsumerState<ReviewsScreen> {
  List<AvaliacaoRecebida> _itens = [];
  String? _erro;

  @override
  void initState() {
    super.initState();
    _carregar();
  }

  Future<void> _carregar() async {
    try {
      final pagina = await ref.read(apiClientProvider).reviewsRecebidas();
      if (mounted) setState(() => _itens = pagina.itens);
    } catch (erro) {
      if (mounted) setState(() => _erro = mensagemErro(erro));
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Avaliações')),
      body: _erro != null
          ? Center(child: Text(_erro!))
          : ListView.builder(
              itemCount: _itens.length,
              itemBuilder: (context, indice) {
                final review = _itens[indice];
                return ListTile(
                  title: Text('${review.anuncioTitulo ?? ''} · ${'★' * review.rating}'),
                  subtitle: Text('${review.autor ?? ''}\n${review.comment ?? ''}\n${review.status ?? ''}'),
                  isThreeLine: true,
                  trailing: review.status == 'pendente'
                      ? Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            IconButton(
                              onPressed: () async {
                                await ref.read(apiClientProvider).moderarReview(review.id, true);
                                await _carregar();
                              },
                              icon: const Icon(Icons.check, color: TopCasaCores.verdeEscuro),
                            ),
                            IconButton(
                              onPressed: () async {
                                await ref.read(apiClientProvider).moderarReview(review.id, false);
                                await _carregar();
                              },
                              icon: const Icon(Icons.close),
                            ),
                          ],
                        )
                      : null,
                );
              },
            ),
    );
  }
}

class NewsletterScreen extends ConsumerStatefulWidget {
  const NewsletterScreen({super.key});

  @override
  ConsumerState<NewsletterScreen> createState() => _NewsletterScreenState();
}

class _NewsletterScreenState extends ConsumerState<NewsletterScreen> {
  final _email = TextEditingController();
  final _nome = TextEditingController();
  String? _erro;

  @override
  void initState() {
    super.initState();
    final user = ref.read(authControllerProvider).utilizador;
    _email.text = user?.email ?? '';
    _nome.text = user?.nome ?? '';
  }

  @override
  void dispose() {
    _email.dispose();
    _nome.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Newsletter')),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          TextField(controller: _nome, decoration: const InputDecoration(labelText: 'Nome')),
          const SizedBox(height: 12),
          TextField(controller: _email, decoration: const InputDecoration(labelText: 'Email')),
          if (_erro != null) Text(_erro!, style: TextStyle(color: Theme.of(context).colorScheme.error)),
          const SizedBox(height: 12),
          FilledButton(
            onPressed: () async {
              try {
                await ref.read(apiClientProvider).subscreverNewsletter(_email.text, _nome.text);
                if (context.mounted) {
                  ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Subscrição confirmada!')));
                  context.pop();
                }
              } catch (erro) {
                setState(() => _erro = mensagemErro(erro));
              }
            },
            child: const Text('Subscrever'),
          ),
        ],
      ),
    );
  }
}
