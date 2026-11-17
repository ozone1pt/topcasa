import 'dart:io';

import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:image_picker/image_picker.dart';
import 'package:material_ui/material_ui.dart';
import 'package:topcasa/core/api_exception.dart';
import 'package:topcasa/core/theme.dart';
import 'package:topcasa/models/social.dart';
import 'package:topcasa/providers/providers.dart';

class RecrutamentoScreen extends ConsumerStatefulWidget {
  const RecrutamentoScreen({super.key, this.meus = false});

  final bool meus;

  @override
  ConsumerState<RecrutamentoScreen> createState() => _RecrutamentoScreenState();
}

class _RecrutamentoScreenState extends ConsumerState<RecrutamentoScreen> {
  List<PedidoResumo> _itens = [];
  var _aCarregar = true;
  String? _erro;

  @override
  void initState() {
    super.initState();
    _carregar();
  }

  Future<void> _carregar() async {
    try {
      final api = ref.read(apiClientProvider);
      final pagina = widget.meus ? await api.meuRecrutamento() : await api.recrutamento();
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
      appBar: AppBar(title: Text(widget.meus ? 'O meu recrutamento' : 'Recrutamento')),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () {
          if (!ref.read(authControllerProvider).autenticado) {
            context.push('/login');
            return;
          }
          context.push('/recrutamento/novo');
        },
        icon: const Icon(Icons.add),
        label: const Text('Publicar'),
      ),
      body: _aCarregar
          ? const Center(child: CircularProgressIndicator(color: TopCasaCores.verde))
          : _erro != null
              ? Center(child: Text(_erro!, textAlign: TextAlign.center))
              : _itens.isEmpty
                  ? const Center(child: Text('Sem recrutamento no momento.'))
                  : RefreshIndicator(
                      onRefresh: _carregar,
                      child: ListView.separated(
                        itemCount: _itens.length,
                        separatorBuilder: (_, _) => const Divider(height: 1),
                        itemBuilder: (context, indice) {
                          final vaga = _itens[indice];
                          return ListTile(
                            title: Text(vaga.titulo),
                            subtitle: Text(
                              '${vaga.empresaNome ?? ''} · ${vaga.distrito ?? ''}'
                              '${vaga.status == null ? '' : ' · ${_rotulo(vaga.status!)}'}'
                              '${vaga.motivoRejeicao == null ? '' : '\n${vaga.motivoRejeicao}'}',
                            ),
                            isThreeLine: vaga.motivoRejeicao != null,
                            onTap: () => context.push('/recrutamento/${vaga.id}', extra: vaga),
                          );
                        },
                      ),
                    ),
    );
  }

  String _rotulo(String status) => switch (status) {
        'pendente' => 'Pendente',
        'ativo' => 'Ativo',
        'pausado' => 'Pausado',
        'rejeitado' => 'Rejeitado',
        'cancelado' => 'Cancelado',
        _ => status,
      };
}

class RecrutamentoDetalheScreen extends ConsumerWidget {
  const RecrutamentoDetalheScreen({super.key, required this.pedido});

  final PedidoResumo pedido;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final meu = pedido.status != null;
    return Scaffold(
      appBar: AppBar(title: const Text('Recrutamento')),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          Text(pedido.titulo, style: Theme.of(context).textTheme.titleLarge),
          Text(pedido.empresaNome ?? ''),
          if (pedido.distrito != null) Text(pedido.distrito!),
          const SizedBox(height: 12),
          Text(pedido.descricao ?? ''),
          const SizedBox(height: 12),
          Text('${pedido.telefone ?? ''} · ${pedido.email ?? ''}'),
          if (pedido.motivoRejeicao != null) Text('Motivo: ${pedido.motivoRejeicao}'),
          if (meu && (pedido.status == 'ativo' || pedido.status == 'pausado'))
            OutlinedButton(
              onPressed: () async {
                await ref.read(apiClientProvider).pausarRecrutamento(pedido.id);
                if (context.mounted) context.pop();
              },
              child: Text(pedido.status == 'ativo' ? 'Pausar' : 'Reativar'),
            ),
          if (meu)
            TextButton(
              onPressed: () async {
                await ref.read(apiClientProvider).apagarRecrutamento(pedido.id);
                if (context.mounted) context.pop();
              },
              child: const Text('Remover'),
            ),
        ],
      ),
    );
  }
}

class RecrutamentoFormScreen extends ConsumerStatefulWidget {
  const RecrutamentoFormScreen({super.key});

  @override
  ConsumerState<RecrutamentoFormScreen> createState() => _RecrutamentoFormScreenState();
}

class _RecrutamentoFormScreenState extends ConsumerState<RecrutamentoFormScreen> {
  final _titulo = TextEditingController();
  final _empresa = TextEditingController();
  final _email = TextEditingController();
  final _telefone = TextEditingController();
  final _descricao = TextEditingController();
  int? _distrito;
  XFile? _foto;
  var _termo = false;
  var _aEnviar = false;
  String? _erro;

  @override
  void dispose() {
    _titulo.dispose();
    _empresa.dispose();
    _email.dispose();
    _telefone.dispose();
    _descricao.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final distritos = ref.watch(distritosProvider);
    return Scaffold(
      appBar: AppBar(title: const Text('Publicar recrutamento')),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          TextField(controller: _titulo, decoration: const InputDecoration(labelText: 'Título')),
          const SizedBox(height: 12),
          TextField(controller: _empresa, decoration: const InputDecoration(labelText: 'Empresa')),
          const SizedBox(height: 12),
          TextField(controller: _email, decoration: const InputDecoration(labelText: 'Email')),
          const SizedBox(height: 12),
          TextField(controller: _telefone, decoration: const InputDecoration(labelText: 'Telefone')),
          const SizedBox(height: 12),
          distritos.when(
            data: (lista) => DropdownButtonFormField<int>(
              initialValue: _distrito,
              decoration: const InputDecoration(labelText: 'Distrito'),
              items: [for (final item in lista) DropdownMenuItem(value: item.id, child: Text(item.nome))],
              onChanged: (valor) => setState(() => _distrito = valor),
            ),
            loading: () => const LinearProgressIndicator(),
            error: (_, _) => const Text('Não foi possível carregar os distritos.'),
          ),
          const SizedBox(height: 12),
          TextField(controller: _descricao, minLines: 4, maxLines: 8, decoration: const InputDecoration(labelText: 'Descrição')),
          CheckboxListTile(
            contentPadding: EdgeInsets.zero,
            value: _termo,
            onChanged: (valor) => setState(() => _termo = valor ?? false),
            title: const Text('Aceito os termos'),
          ),
          TextButton.icon(
            onPressed: () async {
              final foto = await ImagePicker().pickImage(source: ImageSource.gallery, imageQuality: 82, maxWidth: 1400, maxHeight: 1400);
              if (foto != null) setState(() => _foto = foto);
            },
            icon: const Icon(Icons.photo_outlined),
            label: Text(_foto == null ? 'Foto opcional' : 'Foto escolhida'),
          ),
          if (_erro != null) Text(_erro!, style: TextStyle(color: Theme.of(context).colorScheme.error)),
          FilledButton(
            onPressed: _aEnviar
                ? null
                : () async {
                    setState(() => _aEnviar = true);
                    try {
                      await ref.read(apiClientProvider).criarRecrutamento({
                        'titulo': _titulo.text.trim(),
                        'empresa_nome': _empresa.text.trim(),
                        'email': _email.text.trim(),
                        'telefone': _telefone.text.trim(),
                        'distrito_id': _distrito,
                        'descricao': _descricao.text.trim(),
                        'termo_aceite': _termo ? 1 : 0,
                      }, _foto == null ? null : File(_foto!.path));
                      if (context.mounted) context.go('/recrutamento');
                    } catch (erro) {
                      setState(() => _erro = mensagemErro(erro));
                    } finally {
                      if (mounted) setState(() => _aEnviar = false);
                    }
                  },
            child: Text(_aEnviar ? 'A enviar...' : 'Publicar'),
          ),
        ],
      ),
    );
  }
}
