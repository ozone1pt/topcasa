import 'package:material_ui/material_ui.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:topcasa/core/api_exception.dart';
import 'package:topcasa/providers/providers.dart';
import 'package:topcasa/widgets/moldura_auth.dart';

class ForgotPasswordScreen extends ConsumerStatefulWidget {
  const ForgotPasswordScreen({super.key});

  @override
  ConsumerState<ForgotPasswordScreen> createState() => _ForgotPasswordScreenState();
}

class _ForgotPasswordScreenState extends ConsumerState<ForgotPasswordScreen> {
  final _form = GlobalKey<FormState>();
  final _email = TextEditingController();
  var _aEnviar = false;
  String? _erro;
  String? _sucesso;

  @override
  void dispose() {
    _email.dispose();
    super.dispose();
  }

  Future<void> _submeter() async {
    if (!_form.currentState!.validate()) {
      return;
    }
    setState(() {
      _aEnviar = true;
      _erro = null;
      _sucesso = null;
    });
    try {
      final mensagem = await ref.read(apiClientProvider).recuperarPassword(_email.text);
      if (mounted) {
        setState(() => _sucesso = mensagem);
      }
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
      titulo: 'Recuperar palavra-passe',
      child: Form(
        key: _form,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            const Text(
              'Indica o email da conta. Vais receber um link para definires uma palavra-passe nova no site.',
            ),
            const SizedBox(height: 16),
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
            if (_erro != null) ...[
              const SizedBox(height: 12),
              Text(_erro!, style: TextStyle(color: Theme.of(context).colorScheme.error)),
            ],
            if (_sucesso != null) ...[
              const SizedBox(height: 12),
              Text(_sucesso!, style: const TextStyle(color: Color(0xFF15803D))),
            ],
            const SizedBox(height: 16),
            FilledButton(
              onPressed: _aEnviar ? null : _submeter,
              child: Text(_aEnviar ? 'A enviar...' : 'Enviar link'),
            ),
            TextButton(
              onPressed: () => context.go('/login'),
              child: const Text('Voltar ao início de sessão'),
            ),
          ],
        ),
      ),
    );
  }
}
