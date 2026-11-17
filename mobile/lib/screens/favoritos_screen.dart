import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:material_ui/material_ui.dart';
import 'package:topcasa/core/api_exception.dart';
import 'package:topcasa/core/theme.dart';
import 'package:topcasa/models/anuncio.dart';
import 'package:topcasa/providers/providers.dart';
import 'package:topcasa/widgets/anuncio_card.dart';

class FavoritosScreen extends ConsumerStatefulWidget {
  const FavoritosScreen({super.key});

  @override
  ConsumerState<FavoritosScreen> createState() => _FavoritosScreenState();
}

class _FavoritosScreenState extends ConsumerState<FavoritosScreen> {
  List<Anuncio> _itens = [];
  var _aCarregar = true;
  String? _erro;

  @override
  void initState() {
    super.initState();
    _carregar();
  }

  Future<void> _carregar() async {
    try {
      final itens = await ref.read(apiClientProvider).favoritos();
      if (mounted) {
        setState(() {
          _itens = itens;
          _erro = null;
          _aCarregar = false;
        });
      }
    } catch (erro) {
      if (mounted) {
        setState(() {
          _erro = mensagemErro(erro);
          _aCarregar = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Favoritos')),
      body: _aCarregar
          ? const Center(child: CircularProgressIndicator(color: TopCasaCores.verde))
          : _erro != null
              ? Center(child: Text(_erro!))
              : _itens.isEmpty
                  ? const Center(child: Text('Ainda não tens favoritos.'))
                  : ListView(
                      padding: const EdgeInsets.all(16),
                      children: [
                        for (final anuncio in _itens)
                          AnuncioCard(anuncio: anuncio, onTap: () => context.push('/anuncios/${anuncio.id}')),
                      ],
                    ),
    );
  }
}
