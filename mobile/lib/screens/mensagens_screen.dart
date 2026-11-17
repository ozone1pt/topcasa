import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:material_ui/material_ui.dart';
import 'package:topcasa/core/api_exception.dart';
import 'package:topcasa/core/theme.dart';
import 'package:topcasa/models/social.dart';
import 'package:topcasa/providers/providers.dart';

class MensagensScreen extends ConsumerStatefulWidget {
  const MensagensScreen({super.key});

  @override
  ConsumerState<MensagensScreen> createState() => _MensagensScreenState();
}

class _MensagensScreenState extends ConsumerState<MensagensScreen> {
  List<MensagemResumo> _itens = [];
  var _aCarregar = true;
  String? _erro;

  @override
  void initState() {
    super.initState();
    _carregar();
  }

  Future<void> _carregar() async {
    try {
      final caixa = await ref.read(apiClientProvider).mensagens();
      if (mounted) {
        setState(() {
          _itens = caixa.itens;
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
      appBar: AppBar(title: const Text('Mensagens')),
      body: _aCarregar
          ? const Center(child: CircularProgressIndicator(color: TopCasaCores.verde))
          : _erro != null
              ? Center(child: Text(_erro!))
              : _itens.isEmpty
                  ? const Center(child: Text('A caixa de entrada está vazia.'))
                  : RefreshIndicator(
                      onRefresh: _carregar,
                      child: ListView.builder(
                        itemCount: _itens.length,
                        itemBuilder: (context, indice) {
                          final mensagem = _itens[indice];
                          return ListTile(
                            title: Text(mensagem.anuncioTitulo ?? 'Anúncio'),
                            subtitle: Text(mensagem.mensagem, maxLines: 2, overflow: TextOverflow.ellipsis),
                            trailing: mensagem.lida ? null : const Icon(Icons.mark_email_unread, color: TopCasaCores.verde),
                            onTap: () => context.push('/mensagens/${mensagem.id}'),
                          );
                        },
                      ),
                    ),
    );
  }
}

class MensagemScreen extends ConsumerStatefulWidget {
  const MensagemScreen({super.key, required this.id});

  final int id;

  @override
  ConsumerState<MensagemScreen> createState() => _MensagemScreenState();
}

class _MensagemScreenState extends ConsumerState<MensagemScreen> {
  final _resposta = TextEditingController();
  MensagemResumo? _mensagem;
  var _aCarregar = true;
  String? _erro;

  @override
  void initState() {
    super.initState();
    _carregar();
  }

  @override
  void dispose() {
    _resposta.dispose();
    super.dispose();
  }

  Future<void> _carregar() async {
    try {
      final mensagem = await ref.read(apiClientProvider).mensagem(widget.id);
      if (mounted) {
        setState(() {
          _mensagem = mensagem;
          _aCarregar = false;
          _erro = null;
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
    final mensagem = _mensagem;
    return Scaffold(
      appBar: AppBar(
        title: const Text('Conversa'),
        actions: [
          if (mensagem != null)
            IconButton(
              tooltip: 'Apagar',
              onPressed: () async {
                await ref.read(apiClientProvider).apagarMensagem(mensagem.id);
                if (context.mounted) {
                  context.pop();
                }
              },
              icon: const Icon(Icons.delete_outline),
            ),
        ],
      ),
      body: _aCarregar
          ? const Center(child: CircularProgressIndicator(color: TopCasaCores.verde))
          : _erro != null
              ? Center(child: Text(_erro!))
              : Column(
                  children: [
                    Expanded(
                      child: ListView(
                        padding: const EdgeInsets.all(16),
                        children: [
                          Text(mensagem!.remetenteNome ?? 'Mensagem', style: const TextStyle(fontWeight: FontWeight.w700)),
                          const SizedBox(height: 4),
                          Text(mensagem.mensagem),
                          for (final resposta in mensagem.respostas) ...[
                            const Divider(),
                            Text(resposta.remetenteNome ?? 'Resposta', style: const TextStyle(fontWeight: FontWeight.w700)),
                            Text(resposta.mensagem),
                          ],
                        ],
                      ),
                    ),
                    Padding(
                      padding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
                      child: Row(
                        children: [
                          Expanded(
                            child: TextField(
                              controller: _resposta,
                              decoration: const InputDecoration(hintText: 'Responder'),
                            ),
                          ),
                          IconButton(
                            onPressed: () async {
                              final texto = _resposta.text.trim();
                              if (texto.isEmpty) {
                                return;
                              }
                              await ref.read(apiClientProvider).responderMensagem(mensagem.id, texto);
                              _resposta.clear();
                              await _carregar();
                            },
                            icon: const Icon(Icons.send),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
    );
  }
}
