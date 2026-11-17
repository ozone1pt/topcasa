import 'package:material_ui/material_ui.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:topcasa/core/api_exception.dart';
import 'package:topcasa/providers/providers.dart';
import 'package:topcasa/widgets/moldura_auth.dart';

class RegisterScreen extends ConsumerStatefulWidget {
  const RegisterScreen({super.key});

  @override
  ConsumerState<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends ConsumerState<RegisterScreen> {
  final _form = GlobalKey<FormState>();
  final _nome = TextEditingController();
  final _email = TextEditingController();
  final _password = TextEditingController();
  final _confirmacao = TextEditingController();
  var _aEnviar = false;
  String? _erro;

  @override
  void dispose() {
    _nome.dispose();
    _email.dispose();
    _password.dispose();
    _confirmacao.dispose();
    super.dispose();
  }

  Future<void> _submeter() async {
    if (!_form.currentState!.validate()) {
      return;
    }
    setState(() {
      _aEnviar = true;
      _erro = null;
    });
    try {
      await ref.read(authControllerProvider.notifier).registar(
            nome: _nome.text,
            email: _email.text,
            password: _password.text,
            confirmacao: _confirmacao.text,
          );
    } catch (erro) {
      if (mounted) {
        setState(() => _erro = mensagemErro(erro));
      }
    } finally {
      if (mounted) {
        setState(() => _aEnviar = false);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return MolduraAuth(
      titulo: 'Criar conta',
      child: Form(
        key: _form,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            TextFormField(
              controller: _nome,
              textCapitalization: TextCapitalization.words,
              decoration: const InputDecoration(labelText: 'Nome'),
              validator: (valor) => (valor == null || valor.trim().isEmpty) ? 'Indica o teu nome.' : null,
            ),
            const SizedBox(height: 12),
            TextFormField(
              controller: _email,
              keyboardType: TextInputType.emailAddress,
              autocorrect: false,
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
              controller: _password,
              obscureText: true,
              decoration: const InputDecoration(labelText: 'Palavra-passe'),
              validator: (valor) {
                if (valor == null || valor.length < 8) {
                  return 'A palavra-passe tem de ter pelo menos 8 caracteres.';
                }
                return null;
              },
            ),
            const SizedBox(height: 12),
            TextFormField(
              controller: _confirmacao,
              obscureText: true,
              decoration: const InputDecoration(labelText: 'Confirmar palavra-passe'),
              validator: (valor) => valor != _password.text ? 'As palavras-passe não coincidem.' : null,
            ),
            if (_erro != null) ...[
              const SizedBox(height: 12),
              Text(_erro!, style: TextStyle(color: Theme.of(context).colorScheme.error)),
            ],
            const SizedBox(height: 16),
            FilledButton(
              onPressed: _aEnviar ? null : _submeter,
              child: Text(_aEnviar ? 'A criar...' : 'Criar conta'),
            ),
            TextButton(
              onPressed: () => context.go('/login'),
              child: const Text('Já tens conta? Iniciar sessão'),
            ),
          ],
        ),
      ),
    );
  }
}
