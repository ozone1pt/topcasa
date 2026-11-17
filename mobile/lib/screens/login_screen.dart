import 'package:material_ui/material_ui.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:topcasa/core/api_exception.dart';
import 'package:topcasa/providers/providers.dart';
import 'package:topcasa/widgets/moldura_auth.dart';

class LoginScreen extends ConsumerStatefulWidget {
  const LoginScreen({super.key});

  @override
  ConsumerState<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends ConsumerState<LoginScreen> {
  final _form = GlobalKey<FormState>();
  final _login = TextEditingController();
  final _password = TextEditingController();
  var _aEnviar = false;
  var _ocultar = true;
  String? _erro;

  @override
  void dispose() {
    _login.dispose();
    _password.dispose();
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
      await ref.read(authControllerProvider.notifier).login(_login.text, _password.text);
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
      titulo: 'Iniciar sessão',
      child: Form(
        key: _form,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            TextFormField(
              controller: _login,
              textInputAction: TextInputAction.next,
              autocorrect: false,
              decoration: const InputDecoration(labelText: 'Utilizador ou email'),
              validator: (valor) => (valor == null || valor.trim().isEmpty) ? 'Indica o utilizador ou o email.' : null,
            ),
            const SizedBox(height: 12),
            TextFormField(
              controller: _password,
              obscureText: _ocultar,
              decoration: InputDecoration(
                labelText: 'Palavra-passe',
                suffixIcon: IconButton(
                  onPressed: () => setState(() => _ocultar = !_ocultar),
                  icon: Icon(_ocultar ? Icons.visibility_outlined : Icons.visibility_off_outlined),
                ),
              ),
              onFieldSubmitted: (_) => _submeter(),
              validator: (valor) => (valor == null || valor.isEmpty) ? 'Indica a palavra-passe.' : null,
            ),
            if (_erro != null) ...[
              const SizedBox(height: 12),
              Text(_erro!, style: TextStyle(color: Theme.of(context).colorScheme.error)),
            ],
            const SizedBox(height: 16),
            FilledButton(
              onPressed: _aEnviar ? null : _submeter,
              child: Text(_aEnviar ? 'A entrar...' : 'Entrar'),
            ),
            Align(
              alignment: Alignment.centerRight,
              child: TextButton(
                onPressed: () => context.push('/recuperar'),
                child: const Text('Esqueci-me da palavra-passe'),
              ),
            ),
            const Divider(),
            TextButton(
              onPressed: () => context.push('/registar'),
              child: const Text('Ainda não tens conta? Criar conta'),
            ),
            TextButton(
              onPressed: () => context.go('/'),
              child: const Text('Continuar sem conta'),
            ),
          ],
        ),
      ),
    );
  }
}
