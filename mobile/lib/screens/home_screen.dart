import 'package:material_ui/material_ui.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:topcasa/core/theme.dart';
import 'package:topcasa/models/catalogo.dart';
import 'package:topcasa/models/filtro_anuncios.dart';
import 'package:topcasa/providers/providers.dart';
import 'package:topcasa/widgets/anuncio_card.dart';

class HomeScreen extends ConsumerStatefulWidget {
  const HomeScreen({super.key});

  @override
  ConsumerState<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends ConsumerState<HomeScreen> {
  final _scroll = ScrollController();
  final _pesquisa = TextEditingController();

  @override
  void initState() {
    super.initState();
    _scroll.addListener(() {
      if (_scroll.position.pixels >= _scroll.position.maxScrollExtent - 400) {
        ref.read(anunciosControllerProvider.notifier).carregarMais();
      }
    });
    WidgetsBinding.instance.addPostFrameCallback((_) {
      ref.read(anunciosControllerProvider.notifier).carregar();
    });
  }

  @override
  void dispose() {
    _scroll.dispose();
    _pesquisa.dispose();
    super.dispose();
  }

  Future<void> _filtros() async {
    final atual = ref.read(anunciosControllerProvider).filtro;
    final resultado = await showModalBottomSheet<FiltroAnuncios>(
      context: context,
      isScrollControlled: true,
      builder: (context) => _FolhaFiltros(inicial: atual),
    );
    if (resultado == null || !mounted) {
      return;
    }
    _pesquisa.text = resultado.q;
    await ref.read(anunciosControllerProvider.notifier).aplicarFiltro(resultado);
  }

  @override
  Widget build(BuildContext context) {
    final estado = ref.watch(anunciosControllerProvider);
    final auth = ref.watch(authControllerProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text('TopCasa'),
        actions: [
          IconButton(
            tooltip: 'Filtros',
            onPressed: _filtros,
            icon: Icon(estado.filtro.ativo ? Icons.filter_alt : Icons.filter_alt_outlined),
          ),
          PopupMenuButton<String>(
            icon: const Icon(Icons.menu),
            onSelected: (destino) {
              if (destino == '/login') {
                context.push('/login');
                return;
              }
              if (!auth.autenticado) {
                context.push('/login');
                return;
              }
              context.push(destino);
            },
            itemBuilder: (context) => [
              if (!auth.autenticado)
                const PopupMenuItem(value: '/login', child: Text('Entrar'))
              else ...[
                const PopupMenuItem(value: '/anuncios/novo', child: Text('Publicar anúncio')),
                const PopupMenuItem(value: '/meus-anuncios', child: Text('Os meus anúncios')),
                const PopupMenuItem(value: '/favoritos', child: Text('Favoritos')),
                const PopupMenuItem(value: '/mensagens', child: Text('Mensagens')),
                const PopupMenuItem(value: '/alertas', child: Text('Alertas')),
                const PopupMenuItem(value: '/perfil', child: Text('Perfil')),
              ],
            ],
          ),
        ],
      ),
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 12, 16, 4),
            child: TextField(
              controller: _pesquisa,
              textInputAction: TextInputAction.search,
              decoration: const InputDecoration(
                hintText: 'Procurar serviços',
                prefixIcon: Icon(Icons.search),
              ),
              onSubmitted: (valor) {
                final filtro = ref.read(anunciosControllerProvider).filtro;
                ref.read(anunciosControllerProvider.notifier).aplicarFiltro(
                      FiltroAnuncios(
                        categoriaId: filtro.categoriaId,
                        distritoId: filtro.distritoId,
                        concelhoId: filtro.concelhoId,
                        q: valor,
                      ),
                    );
              },
            ),
          ),
          Expanded(
            child: RefreshIndicator(
              color: TopCasaCores.verde,
              onRefresh: () => ref.read(anunciosControllerProvider.notifier).carregar(),
              child: _corpo(estado),
            ),
          ),
        ],
      ),
    );
  }

  Widget _corpo(EstadoAnuncios estado) {
    if (estado.aCarregar && estado.itens.isEmpty) {
      return const Center(child: CircularProgressIndicator(color: TopCasaCores.verde));
    }

    if (estado.erro != null && estado.itens.isEmpty) {
      return ListView(
        physics: const AlwaysScrollableScrollPhysics(),
        children: [
          const SizedBox(height: 80),
          Icon(Icons.cloud_off_outlined, size: 42, color: Theme.of(context).colorScheme.error),
          const SizedBox(height: 12),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 32),
            child: Text(estado.erro!, textAlign: TextAlign.center),
          ),
          const SizedBox(height: 12),
          Center(
            child: TextButton(
              onPressed: () => ref.read(anunciosControllerProvider.notifier).carregar(),
              child: const Text('Tentar outra vez'),
            ),
          ),
        ],
      );
    }

    if (estado.vazio) {
      return ListView(
        physics: const AlwaysScrollableScrollPhysics(),
        children: const [
          SizedBox(height: 80),
          Icon(Icons.search_off, size: 42, color: TopCasaCores.textoSuave),
          SizedBox(height: 12),
          Text('Nenhum anúncio encontrado.', textAlign: TextAlign.center),
        ],
      );
    }

    return ListView.builder(
      controller: _scroll,
      physics: const AlwaysScrollableScrollPhysics(),
      padding: const EdgeInsets.fromLTRB(16, 8, 16, 24),
      itemCount: estado.itens.length + (estado.aCarregarMais ? 1 : 0),
      itemBuilder: (context, indice) {
        if (indice >= estado.itens.length) {
          return const Padding(
            padding: EdgeInsets.all(16),
            child: Center(child: CircularProgressIndicator(color: TopCasaCores.verde)),
          );
        }
        final anuncio = estado.itens[indice];
        return AnuncioCard(
          anuncio: anuncio,
          onTap: () => context.push('/anuncios/${anuncio.id}'),
        );
      },
    );
  }
}

class _FolhaFiltros extends ConsumerStatefulWidget {
  const _FolhaFiltros({required this.inicial});

  final FiltroAnuncios inicial;

  @override
  ConsumerState<_FolhaFiltros> createState() => _FolhaFiltrosState();
}

class _FolhaFiltrosState extends ConsumerState<_FolhaFiltros> {
  late int? _categoria;
  late int? _distrito;
  late int? _concelho;
  List<Concelho> _concelhos = const [];
  var _aCarregarConcelhos = false;

  @override
  void initState() {
    super.initState();
    _categoria = widget.inicial.categoriaId;
    _distrito = widget.inicial.distritoId;
    _concelho = widget.inicial.concelhoId;
    if (_distrito != null) {
      _carregarConcelhos(_distrito!, manter: true);
    }
  }

  Future<void> _carregarConcelhos(int distritoId, {bool manter = false}) async {
    setState(() {
      _aCarregarConcelhos = true;
      if (!manter) {
        _concelho = null;
      }
    });
    try {
      final lista = await ref.read(apiClientProvider).concelhos(distritoId);
      if (mounted) {
        setState(() => _concelhos = lista);
      }
    } catch (_) {
      if (mounted) {
        setState(() => _concelhos = const []);
      }
    } finally {
      if (mounted) {
        setState(() => _aCarregarConcelhos = false);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final distritos = ref.watch(distritosProvider);
    final categorias = ref.watch(categoriasProvider);

    return Padding(
      padding: EdgeInsets.fromLTRB(20, 16, 20, 20 + MediaQuery.viewInsetsOf(context).bottom),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Text('Filtros', style: Theme.of(context).textTheme.titleLarge),
          const SizedBox(height: 16),
          categorias.when(
            data: (lista) => DropdownButtonFormField<int?>(
              initialValue: _categoria,
              decoration: const InputDecoration(labelText: 'Categoria'),
              items: [
                const DropdownMenuItem<int?>(value: null, child: Text('Todas as categorias')),
                ...lista.map((c) => DropdownMenuItem(value: c.id, child: Text(c.nome))),
              ],
              onChanged: (valor) => setState(() => _categoria = valor),
            ),
            loading: () => const LinearProgressIndicator(),
            error: (_, _) => const Text('Não foi possível carregar as categorias.'),
          ),
          const SizedBox(height: 12),
          distritos.when(
            data: (lista) => DropdownButtonFormField<int?>(
              initialValue: _distrito,
              decoration: const InputDecoration(labelText: 'Distrito'),
              items: [
                const DropdownMenuItem<int?>(value: null, child: Text('Todos os distritos')),
                ...lista.map((d) => DropdownMenuItem(value: d.id, child: Text(d.nome))),
              ],
              onChanged: (valor) {
                setState(() {
                  _distrito = valor;
                  _concelho = null;
                  _concelhos = const [];
                });
                if (valor != null) {
                  _carregarConcelhos(valor);
                }
              },
            ),
            loading: () => const LinearProgressIndicator(),
            error: (_, _) => const Text('Não foi possível carregar os distritos.'),
          ),
          const SizedBox(height: 12),
          if (_distrito != null)
            DropdownButtonFormField<int?>(
              initialValue: _concelhos.any((c) => c.id == _concelho) ? _concelho : null,
              decoration: InputDecoration(
                labelText: 'Concelho',
                suffixIcon: _aCarregarConcelhos
                    ? const Padding(
                        padding: EdgeInsets.all(12),
                        child: SizedBox(width: 16, height: 16, child: CircularProgressIndicator(strokeWidth: 2)),
                      )
                    : null,
              ),
              items: [
                const DropdownMenuItem<int?>(value: null, child: Text('Todos os concelhos')),
                ..._concelhos.map((c) => DropdownMenuItem(value: c.id, child: Text(c.nome))),
              ],
              onChanged: _aCarregarConcelhos ? null : (valor) => setState(() => _concelho = valor),
            ),
          const SizedBox(height: 20),
          FilledButton(
            onPressed: () {
              Navigator.pop(
                context,
                FiltroAnuncios(
                  categoriaId: _categoria,
                  distritoId: _distrito,
                  concelhoId: _concelho,
                  q: widget.inicial.q,
                ),
              );
            },
            child: const Text('Aplicar'),
          ),
          TextButton(
            onPressed: () => Navigator.pop(context, const FiltroAnuncios()),
            child: const Text('Limpar filtros'),
          ),
        ],
      ),
    );
  }
}
