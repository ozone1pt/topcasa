import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:material_ui/material_ui.dart';
import 'package:topcasa/core/api_exception.dart';
import 'package:topcasa/core/theme.dart';
import 'package:topcasa/models/social.dart';
import 'package:topcasa/providers/providers.dart';

class BlogScreen extends ConsumerStatefulWidget {
  const BlogScreen({super.key});

  @override
  ConsumerState<BlogScreen> createState() => _BlogScreenState();
}

class _BlogScreenState extends ConsumerState<BlogScreen> {
  List<Artigo> _itens = [];
  String? _erro;

  @override
  void initState() {
    super.initState();
    _carregar();
  }

  Future<void> _carregar() async {
    try {
      final pagina = await ref.read(apiClientProvider).blog();
      if (mounted) setState(() => _itens = pagina.itens);
    } catch (erro) {
      if (mounted) setState(() => _erro = mensagemErro(erro));
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Blog')),
      body: _erro != null
          ? Center(child: Text(_erro!))
          : ListView.builder(
              itemCount: _itens.length,
              itemBuilder: (context, indice) {
                final artigo = _itens[indice];
                return ListTile(title: Text(artigo.titulo), onTap: () => context.push('/blog/${artigo.slug}'));
              },
            ),
    );
  }
}

class ArtigoScreen extends ConsumerStatefulWidget {
  const ArtigoScreen({super.key, required this.slug});

  final String slug;

  @override
  ConsumerState<ArtigoScreen> createState() => _ArtigoScreenState();
}

class _ArtigoScreenState extends ConsumerState<ArtigoScreen> {
  Artigo? _artigo;
  String? _erro;

  @override
  void initState() {
    super.initState();
    _carregar();
  }

  Future<void> _carregar() async {
    try {
      final artigo = await ref.read(apiClientProvider).artigo(widget.slug);
      if (mounted) setState(() => _artigo = artigo);
    } catch (erro) {
      if (mounted) setState(() => _erro = mensagemErro(erro));
    }
  }

  @override
  Widget build(BuildContext context) {
    final artigo = _artigo;
    return Scaffold(
      appBar: AppBar(title: const Text('Artigo')),
      body: _erro != null
          ? Center(child: Text(_erro!))
          : artigo == null
              ? const Center(child: CircularProgressIndicator(color: TopCasaCores.verde))
              : ListView(
                  padding: const EdgeInsets.all(16),
                  children: [
                    Text(artigo.titulo, style: Theme.of(context).textTheme.headlineSmall),
                    const SizedBox(height: 12),
                    Text((artigo.conteudo ?? '').replaceAll(RegExp(r'<[^>]+>'), ' ')),
                  ],
                ),
    );
  }
}
