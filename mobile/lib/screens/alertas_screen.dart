import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:material_ui/material_ui.dart';
import 'package:topcasa/core/api_exception.dart';
import 'package:topcasa/core/theme.dart';
import 'package:topcasa/models/social.dart';
import 'package:topcasa/providers/providers.dart';

class AlertasScreen extends ConsumerStatefulWidget {
  const AlertasScreen({super.key});

  @override
  ConsumerState<AlertasScreen> createState() => _AlertasScreenState();
}

class _AlertasScreenState extends ConsumerState<AlertasScreen> {
  List<AlertaResumo> _itens = [];
  var _aCarregar = true;
  String? _erro;

  @override
  void initState() {
    super.initState();
    _carregar();
  }

  Future<void> _carregar() async {
    try {
      final pagina = await ref.read(apiClientProvider).alertas();
      if (mounted) {
        setState(() {
          _itens = pagina.itens;
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
      appBar: AppBar(title: const Text('Alertas')),
      body: _aCarregar
          ? const Center(child: CircularProgressIndicator(color: TopCasaCores.verde))
          : _erro != null
              ? Center(child: Text(_erro!))
              : _itens.isEmpty
                  ? const Center(child: Text('Não tens alertas.'))
                  : ListView.builder(
                      itemCount: _itens.length,
                      itemBuilder: (context, indice) {
                        final alerta = _itens[indice];
                        return ListTile(
                          title: Text(alerta.titulo),
                          subtitle: Text(alerta.mensagem),
                          trailing: alerta.lida ? null : const Icon(Icons.circle, size: 10, color: TopCasaCores.verde),
                          onTap: () async {
                            await ref.read(apiClientProvider).marcarAlertaLido(alerta.id);
                            await _carregar();
                          },
                        );
                      },
                    ),
    );
  }
}
