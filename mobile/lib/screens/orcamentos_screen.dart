import 'dart:io';

import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:image_picker/image_picker.dart';
import 'package:material_ui/material_ui.dart';
import 'package:topcasa/core/api_exception.dart';
import 'package:topcasa/core/theme.dart';
import 'package:topcasa/models/social.dart';
import 'package:topcasa/providers/providers.dart';

class OrcamentosScreen extends ConsumerStatefulWidget {
  const OrcamentosScreen({super.key, this.meus = false});

  final bool meus;

  @override
  ConsumerState<OrcamentosScreen> createState() => _OrcamentosScreenState();
}

class _OrcamentosScreenState extends ConsumerState<OrcamentosScreen> {
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
      final pagina = widget.meus ? await api.meusOrcamentos() : await api.orcamentos();
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
      appBar: AppBar(title: Text(widget.meus ? 'Os meus orçamentos' : 'Orçamentos')),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () {
          final auth = ref.read(authControllerProvider);
          if (!auth.autenticado) {
            context.push('/login');
            return;
          }
          context.push('/orcamentos/novo');
        },
        icon: const Icon(Icons.add),
        label: const Text('Pedir'),
      ),
      body: _corpo(),
    );
  }

  Widget _corpo() {
    if (_aCarregar) return const Center(child: CircularProgressIndicator(color: TopCasaCores.verde));
    if (_erro != null) return Center(child: Text(_erro!, textAlign: TextAlign.center));
    if (_itens.isEmpty) return const Center(child: Text('Sem pedidos de orçamento.'));
    return RefreshIndicator(
      onRefresh: _carregar,
      child: ListView.separated(
        itemCount: _itens.length,
        separatorBuilder: (_, _) => const Divider(height: 1),
        itemBuilder: (context, indice) {
          final pedido = _itens[indice];
          return ListTile(
            title: Text(pedido.titulo),
            subtitle: Text('${pedido.distrito ?? ''}${pedido.status == null ? '' : ' · ${_rotulo(pedido.status!)}'}'),
            onTap: () => context.push('/orcamentos/${pedido.id}', extra: pedido),
          );
        },
      ),
    );
  }

  String _rotulo(String status) => switch (status) {
        'pendente' => 'Pendente',
        'validado' => 'Aprovado',
        'rejeitado' => 'Rejeitado',
        'concluido' => 'Concluído',
        _ => status,
      };
}

class OrcamentoDetalheScreen extends ConsumerStatefulWidget {
  const OrcamentoDetalheScreen({super.key, required this.pedido});

  final PedidoResumo pedido;

  @override
  ConsumerState<OrcamentoDetalheScreen> createState() => _OrcamentoDetalheScreenState();
}

class _OrcamentoDetalheScreenState extends ConsumerState<OrcamentoDetalheScreen> {
  Map<String, dynamic>? _contacto;
  String? _erro;

  @override
  Widget build(BuildContext context) {
    final pedido = widget.pedido;
    return Scaffold(
      appBar: AppBar(title: const Text('Orçamento')),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          Text(pedido.titulo, style: Theme.of(context).textTheme.titleLarge),
          if (pedido.distrito != null) Text(pedido.distrito!),
          const SizedBox(height: 12),
          Text(pedido.descricao ?? ''),
          if (pedido.motivoRejeicao != null) ...[
            const SizedBox(height: 12),
            Text('Motivo: ${pedido.motivoRejeicao}'),
          ],
          const SizedBox(height: 16),
          if (_contacto == null)
            FilledButton(
              onPressed: () async {
                try {
                  final dados = await ref.read(apiClientProvider).desbloquearOrcamento(pedido.id);
                  setState(() => _contacto = dados);
                } catch (erro) {
                  setState(() => _erro = mensagemErro(erro));
                }
              },
              child: const Text('Ver contacto'),
            )
          else
            Text('${_contacto!['nome'] ?? ''} · ${_contacto!['telefone'] ?? ''} · ${_contacto!['email'] ?? ''}'),
          if (pedido.status == 'validado')
            OutlinedButton(
              onPressed: () async {
                await ref.read(apiClientProvider).concluirOrcamento(pedido.id);
                if (context.mounted) context.pop();
              },
              child: const Text('Marcar como concluído'),
            ),
          if (_erro != null) Text(_erro!, style: TextStyle(color: Theme.of(context).colorScheme.error)),
        ],
      ),
    );
  }
}

class OrcamentoFormScreen extends ConsumerStatefulWidget {
  const OrcamentoFormScreen({super.key});

  @override
  ConsumerState<OrcamentoFormScreen> createState() => _OrcamentoFormScreenState();
}

class _OrcamentoFormScreenState extends ConsumerState<OrcamentoFormScreen> {
  final _titulo = TextEditingController();
  final _nome = TextEditingController();
  final _telefone = TextEditingController();
  final _email = TextEditingController();
  final _descricao = TextEditingController();
  int? _distrito;
  XFile? _foto;
  var _termo = false;
  var _aEnviar = false;
  String? _erro;

  @override
  void initState() {
    super.initState();
    final user = ref.read(authControllerProvider).utilizador;
    _nome.text = user?.nome ?? '';
    _email.text = user?.email ?? '';
    _telefone.text = user?.telefone ?? '';
  }

  @override
  void dispose() {
    _titulo.dispose();
    _nome.dispose();
    _telefone.dispose();
    _email.dispose();
    _descricao.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final distritos = ref.watch(distritosProvider);
    return Scaffold(
      appBar: AppBar(title: const Text('Pedir orçamento')),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          TextField(controller: _titulo, decoration: const InputDecoration(labelText: 'Título')),
          const SizedBox(height: 12),
          TextField(controller: _nome, decoration: const InputDecoration(labelText: 'Nome')),
          const SizedBox(height: 12),
          TextField(controller: _telefone, decoration: const InputDecoration(labelText: 'Telefone')),
          const SizedBox(height: 12),
          TextField(controller: _email, decoration: const InputDecoration(labelText: 'Email')),
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
                      await ref.read(apiClientProvider).criarOrcamento({
                        'titulo': _titulo.text.trim(),
                        'nome': _nome.text.trim(),
                        'telefone': _telefone.text.trim(),
                        'email': _email.text.trim(),
                        'distrito_id': _distrito,
                        'descricao': _descricao.text.trim(),
                        'termo_aceite': _termo ? 1 : 0,
                      }, _foto == null ? null : File(_foto!.path));
                      if (context.mounted) context.go('/conta');
                    } catch (erro) {
                      setState(() => _erro = mensagemErro(erro));
                    } finally {
                      if (mounted) setState(() => _aEnviar = false);
                    }
                  },
            child: Text(_aEnviar ? 'A enviar...' : 'Submeter'),
          ),
        ],
      ),
    );
  }
}
