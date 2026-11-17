import 'package:material_ui/material_ui.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:topcasa/core/api_exception.dart';
import 'package:topcasa/core/theme.dart';
import 'package:topcasa/models/user.dart';
import 'package:topcasa/providers/providers.dart';

class ProfileScreen extends ConsumerStatefulWidget {
  const ProfileScreen({super.key});

  @override
  ConsumerState<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends ConsumerState<ProfileScreen> {
  final _form = GlobalKey<FormState>();
  late final TextEditingController _nome;
  late final TextEditingController _apelido;
  late final TextEditingController _email;
  late final TextEditingController _telefone;
  late final TextEditingController _nif;
  late final TextEditingController _empresa;
  String? _tipo;
  var _aEditar = false;
  var _aGuardar = false;
  String? _erro;

  @override
  void initState() {
    super.initState();
    final utilizador = ref.read(authControllerProvider).utilizador;
    _nome = TextEditingController(text: utilizador?.nome ?? '');
    _apelido = TextEditingController(text: utilizador?.apelido ?? '');
    _email = TextEditingController(text: utilizador?.email ?? '');
    _telefone = TextEditingController(text: utilizador?.telefone ?? '');
    _nif = TextEditingController(text: utilizador?.nif ?? '');
    _empresa = TextEditingController(text: utilizador?.empresaNome ?? '');
    _tipo = utilizador?.tipoConta;
  }

  @override
  void dispose() {
    _nome.dispose();
    _apelido.dispose();
    _email.dispose();
    _telefone.dispose();
    _nif.dispose();
    _empresa.dispose();
    super.dispose();
  }

  Future<void> _guardar() async {
    if (!_form.currentState!.validate()) {
      return;
    }
    setState(() {
      _aGuardar = true;
      _erro = null;
    });
    String? vazio(String texto) => texto.trim().isEmpty ? null : texto.trim();
    try {
      final atualizado = await ref.read(apiClientProvider).atualizarPerfil({
        'nome': _nome.text.trim(),
        'apelido': vazio(_apelido.text),
        'email': _email.text.trim().toLowerCase(),
        'telefone': vazio(_telefone.text),
        'nif': vazio(_nif.text),
        'tipo_conta': _tipo,
        'empresa_nome': _tipo == 'empresa' ? vazio(_empresa.text) : vazio(_empresa.text),
      });
      await ref.read(authControllerProvider.notifier).atualizar(atualizado);
      if (mounted) {
        setState(() => _aEditar = false);
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Perfil atualizado.')));
      }
    } catch (erro) {
      if (mounted) {
        setState(() => _erro = mensagemErro(erro));
      }
    } finally {
      if (mounted) {
        setState(() => _aGuardar = false);
      }
    }
  }

  Future<void> _sair() async {
    final confirmar = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Terminar sessão'),
        content: const Text('Queres sair desta conta neste telemóvel?'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context, false), child: const Text('Cancelar')),
          FilledButton(onPressed: () => Navigator.pop(context, true), child: const Text('Sair')),
        ],
      ),
    );
    if (confirmar != true || !mounted) {
      return;
    }
    await ref.read(authControllerProvider.notifier).terminarSessao();
    if (mounted) {
      context.go('/');
    }
  }

  @override
  Widget build(BuildContext context) {
    final utilizador = ref.watch(authControllerProvider).utilizador;
    if (utilizador == null) {
      return const Scaffold(body: SizedBox.shrink());
    }

    return Scaffold(
      appBar: AppBar(
        title: const Text('O meu perfil'),
        actions: [
          IconButton(
            tooltip: _aEditar ? 'Cancelar' : 'Editar',
            onPressed: () => setState(() => _aEditar = !_aEditar),
            icon: Icon(_aEditar ? Icons.close : Icons.edit_outlined),
          ),
        ],
      ),
      body: ListView(
        padding: const EdgeInsets.all(20),
        children: [
          Text(utilizador.nomeApresentacao, style: Theme.of(context).textTheme.headlineSmall?.copyWith(fontWeight: FontWeight.w700)),
          Text('@${utilizador.username}', style: const TextStyle(color: TopCasaCores.textoSuave)),
          const SizedBox(height: 20),
          if (!_aEditar) _resumo(utilizador) else _formulario(),
          const SizedBox(height: 24),
          OutlinedButton.icon(
            onPressed: _sair,
            icon: const Icon(Icons.logout),
            label: const Text('Terminar sessão'),
          ),
        ],
      ),
    );
  }

  Widget _resumo(Utilizador utilizador) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            _linha('Email', utilizador.email),
            _linha('Telefone', utilizador.telefone),
            _linha('NIF', utilizador.nif),
            _linha('Tipo de conta', _rotuloTipo(utilizador.tipoConta)),
            if (utilizador.tipoConta == 'empresa') _linha('Empresa', utilizador.empresaNome),
          ],
        ),
      ),
    );
  }

  Widget _formulario() {
    return Form(
      key: _form,
      child: Column(
        children: [
          TextFormField(
            controller: _nome,
            decoration: const InputDecoration(labelText: 'Nome'),
            validator: (valor) => (valor == null || valor.trim().isEmpty) ? 'O nome é obrigatório.' : null,
          ),
          const SizedBox(height: 12),
          TextFormField(
            controller: _apelido,
            decoration: const InputDecoration(labelText: 'Apelido'),
          ),
          const SizedBox(height: 12),
          TextFormField(
            controller: _email,
            keyboardType: TextInputType.emailAddress,
            decoration: const InputDecoration(labelText: 'Email'),
            validator: (valor) {
              final email = valor?.trim() ?? '';
              if (email.isEmpty || !email.contains('@')) {
                return 'Indica um email válido.';
              }
              return null;
            },
          ),
          const SizedBox(height: 12),
          TextFormField(
            controller: _telefone,
            keyboardType: TextInputType.phone,
            decoration: const InputDecoration(labelText: 'Telefone'),
          ),
          const SizedBox(height: 12),
          TextFormField(
            controller: _nif,
            keyboardType: TextInputType.number,
            decoration: const InputDecoration(labelText: 'NIF'),
            validator: (valor) {
              final nif = valor?.trim() ?? '';
              if (nif.isEmpty) {
                return null;
              }
              if (!RegExp(r'^\d{9}$').hasMatch(nif)) {
                return 'O NIF tem de ter 9 dígitos.';
              }
              return null;
            },
          ),
          const SizedBox(height: 12),
          DropdownButtonFormField<String?>(
            initialValue: _tipo,
            decoration: const InputDecoration(labelText: 'Tipo de conta'),
            items: const [
              DropdownMenuItem(value: null, child: Text('—')),
              DropdownMenuItem(value: 'particular', child: Text('Particular')),
              DropdownMenuItem(value: 'profissional', child: Text('Profissional')),
              DropdownMenuItem(value: 'empresa', child: Text('Empresa')),
            ],
            onChanged: (valor) => setState(() => _tipo = valor),
          ),
          if (_tipo == 'empresa') ...[
            const SizedBox(height: 12),
            TextFormField(
              controller: _empresa,
              decoration: const InputDecoration(labelText: 'Nome da empresa'),
              validator: (valor) =>
                  (valor == null || valor.trim().isEmpty) ? 'Indica o nome da empresa.' : null,
            ),
          ],
          if (_erro != null) ...[
            const SizedBox(height: 12),
            Text(_erro!, style: TextStyle(color: Theme.of(context).colorScheme.error)),
          ],
          const SizedBox(height: 16),
          FilledButton(
            onPressed: _aGuardar ? null : _guardar,
            child: Text(_aGuardar ? 'A guardar...' : 'Guardar'),
          ),
        ],
      ),
    );
  }

  Widget _linha(String rotulo, String? valor) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(width: 120, child: Text(rotulo, style: const TextStyle(color: TopCasaCores.textoSuave))),
          Expanded(child: Text((valor == null || valor.isEmpty) ? '—' : valor)),
        ],
      ),
    );
  }

  String? _rotuloTipo(String? tipo) {
    return switch (tipo) {
      'particular' => 'Particular',
      'profissional' => 'Profissional',
      'empresa' => 'Empresa',
      _ => null,
    };
  }
}
