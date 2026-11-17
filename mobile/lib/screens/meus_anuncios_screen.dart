import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:material_ui/material_ui.dart';
import 'package:topcasa/core/api_exception.dart';
import 'package:topcasa/core/theme.dart';
import 'package:topcasa/models/anuncio.dart';
import 'package:topcasa/providers/providers.dart';

class MeusAnunciosScreen extends ConsumerStatefulWidget {
  const MeusAnunciosScreen({super.key});

  @override
  ConsumerState<MeusAnunciosScreen> createState() => _MeusAnunciosScreenState();
}

class _MeusAnunciosScreenState extends ConsumerState<MeusAnunciosScreen> {
  List<Anuncio> _itens = [];
  var _aCarregar = true;
  String? _erro;

  @override
  void initState() {
    super.initState();
    _carregar();
  }

  Future<void> _carregar() async {
    setState(() {
      _aCarregar = true;
      _erro = null;
    });
    try {
      final pagina = await ref.read(apiClientProvider).meusAnuncios();
      if (mounted) {
        setState(() => _itens = pagina.itens);
      }
    } catch (erro) {
      if (mounted) {
        setState(() => _erro = mensagemErro(erro));
      }
    } finally {
      if (mounted) {
        setState(() => _aCarregar = false);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Os meus anúncios')),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () => context.push('/anuncios/novo'),
        icon: const Icon(Icons.add),
        label: const Text('Publicar'),
      ),
      body: _aCarregar
          ? const Center(child: CircularProgressIndicator(color: TopCasaCores.verde))
          : _erro != null
              ? Center(child: Text(_erro!, textAlign: TextAlign.center))
              : RefreshIndicator(
                  onRefresh: _carregar,
                  child: _itens.isEmpty
                      ? ListView(children: const [SizedBox(height: 80), Center(child: Text('Ainda não publicaste anúncios.'))])
                      : ListView.builder(
                          itemCount: _itens.length,
                          itemBuilder: (context, indice) {
                            final anuncio = _itens[indice];
                            return ListTile(
                              title: Text(anuncio.titulo),
                              subtitle: Text(_estado(anuncio)),
                              trailing: PopupMenuButton<String>(
                                onSelected: (acao) => _acao(acao, anuncio),
                                itemBuilder: (context) => [
                                  const PopupMenuItem(value: 'ver', child: Text('Ver')),
                                  const PopupMenuItem(value: 'editar', child: Text('Editar')),
                                  if (anuncio.status == 'expirado')
                                    const PopupMenuItem(value: 'reativar', child: Text('Reativar')),
                                  const PopupMenuItem(value: 'apagar', child: Text('Apagar')),
                                ],
                              ),
                              onTap: () => context.push('/anuncios/${anuncio.id}'),
                            );
                          },
                        ),
                ),
    );
  }

  String _estado(Anuncio anuncio) {
    final base = switch (anuncio.status) {
      'aprovado' => 'Aprovado',
      'pendente' => 'Pendente de aprovação',
      'rejeitado' => 'Rejeitado',
      'expirado' => 'Expirado',
      'rascunho' => 'Rascunho',
      _ => anuncio.status ?? '',
    };
    if (anuncio.motivoRejeicao != null && anuncio.motivoRejeicao!.isNotEmpty) {
      return '$base — ${anuncio.motivoRejeicao}';
    }
    return base;
  }

  Future<void> _acao(String acao, Anuncio anuncio) async {
    final api = ref.read(apiClientProvider);
    try {
      switch (acao) {
        case 'ver':
          if (mounted) context.push('/anuncios/${anuncio.id}');
        case 'editar':
          if (mounted) context.push('/anuncios/${anuncio.id}/editar');
        case 'reativar':
          await api.reativarAnuncio(anuncio.id);
          await _carregar();
        case 'apagar':
          await api.apagarAnuncio(anuncio.id);
          await _carregar();
      }
    } catch (erro) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(mensagemErro(erro))));
      }
    }
  }
}
