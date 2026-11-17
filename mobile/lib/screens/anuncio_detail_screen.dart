import 'package:material_ui/material_ui.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:topcasa/core/api_exception.dart';
import 'package:topcasa/core/theme.dart';
import 'package:topcasa/models/anuncio.dart';
import 'package:topcasa/providers/providers.dart';
import 'package:url_launcher/url_launcher.dart';

class AnuncioDetailScreen extends ConsumerWidget {
  const AnuncioDetailScreen({super.key, required this.id});

  final int id;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final anuncio = ref.watch(anuncioDetalheProvider(id));

    return Scaffold(
      appBar: AppBar(title: const Text('Anúncio')),
      body: anuncio.when(
        data: (item) => _Detalhe(anuncio: item),
        loading: () => const Center(child: CircularProgressIndicator(color: TopCasaCores.verde)),
        error: (erro, _) => Center(
          child: Padding(
            padding: const EdgeInsets.all(24),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                const Text('Não foi possível abrir este anúncio.', textAlign: TextAlign.center),
                const SizedBox(height: 12),
                TextButton(
                  onPressed: () => ref.invalidate(anuncioDetalheProvider(id)),
                  child: const Text('Tentar outra vez'),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

class _Detalhe extends StatefulWidget {
  const _Detalhe({required this.anuncio});

  final Anuncio anuncio;

  @override
  State<_Detalhe> createState() => _DetalheState();
}

class _DetalheState extends State<_Detalhe> {
  final _pagina = PageController();
  var _indice = 0;

  @override
  void dispose() {
    _pagina.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final anuncio = widget.anuncio;
    final imagens = anuncio.imagens;

    return ListView(
      children: [
        SizedBox(
          height: 260,
          child: imagens.isEmpty
              ? const ColoredBox(
                  color: TopCasaCores.fundo,
                  child: Center(
                    child: Image(
                      image: AssetImage('assets/images/logo-topcasa-icon-square.png'),
                      height: 72,
                    ),
                  ),
                )
              : Stack(
                  children: [
                    PageView.builder(
                      controller: _pagina,
                      itemCount: imagens.length,
                      onPageChanged: (valor) => setState(() => _indice = valor),
                      itemBuilder: (context, indice) => Image.network(
                        imagens[indice],
                        fit: BoxFit.cover,
                        width: double.infinity,
                        errorBuilder: (_, _, _) => const ColoredBox(
                          color: TopCasaCores.fundo,
                          child: Icon(Icons.broken_image_outlined, color: TopCasaCores.textoSuave),
                        ),
                      ),
                    ),
                    if (imagens.length > 1)
                      Positioned(
                        right: 12,
                        bottom: 12,
                        child: DecoratedBox(
                          decoration: BoxDecoration(
                            color: Colors.black54,
                            borderRadius: BorderRadius.circular(20),
                          ),
                          child: Padding(
                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                            child: Text(
                              '${_indice + 1}/${imagens.length}',
                              style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w600),
                            ),
                          ),
                        ),
                      ),
                  ],
                ),
        ),
        Padding(
          padding: const EdgeInsets.all(20),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              if (anuncio.categoria != null)
                Text(
                  anuncio.categoria!.toUpperCase(),
                  style: const TextStyle(
                    color: TopCasaCores.verdeEscuro,
                    fontWeight: FontWeight.w700,
                    letterSpacing: 0.6,
                    fontSize: 12,
                  ),
                ),
              const SizedBox(height: 6),
              Text(anuncio.titulo, style: Theme.of(context).textTheme.headlineSmall?.copyWith(fontWeight: FontWeight.w700)),
              if (anuncio.nomeProfissional.isNotEmpty) ...[
                const SizedBox(height: 8),
                Text(anuncio.nomeProfissional, style: const TextStyle(fontSize: 16)),
              ],
              if (anuncio.localizacao.isNotEmpty) ...[
                const SizedBox(height: 8),
                Row(
                  children: [
                    const Icon(Icons.place_outlined, size: 18, color: TopCasaCores.textoSuave),
                    const SizedBox(width: 4),
                    Expanded(child: Text(anuncio.localizacao, style: const TextStyle(color: TopCasaCores.textoSuave))),
                  ],
                ),
              ],
              const SizedBox(height: 12),
              Wrap(
                spacing: 8,
                runSpacing: 8,
                children: [
                  if (anuncio.certificacao) const _Selo(texto: 'Certificação profissional'),
                  if (anuncio.seguro) const _Selo(texto: 'Seguro de responsabilidade'),
                  if (anuncio.destacar) const _Selo(texto: 'Em destaque'),
                ],
              ),
              const SizedBox(height: 16),
              Text(
                (anuncio.descricao == null || anuncio.descricao!.trim().isEmpty)
                    ? 'Este anúncio não tem descrição.'
                    : anuncio.descricao!,
                style: const TextStyle(height: 1.45, fontSize: 15),
              ),
              const SizedBox(height: 20),
              _AcoesAnuncio(anuncio: anuncio),
            ],
          ),
        ),
      ],
    );
  }
}

class _AcoesAnuncio extends ConsumerStatefulWidget {
  const _AcoesAnuncio({required this.anuncio});

  final Anuncio anuncio;

  @override
  ConsumerState<_AcoesAnuncio> createState() => _AcoesAnuncioState();
}

class _AcoesAnuncioState extends ConsumerState<_AcoesAnuncio> {
  final _mensagem = TextEditingController();
  final _comentario = TextEditingController();
  String? _telefone;
  String? _erro;
  var _ocupado = false;
  var _estrelas = 5;

  @override
  void dispose() {
    _mensagem.dispose();
    _comentario.dispose();
    super.dispose();
  }

  Future<void> _correr(Future<void> Function() acao) async {
    setState(() {
      _ocupado = true;
      _erro = null;
    });
    try {
      await acao();
    } catch (erro) {
      if (mounted) {
        setState(() => _erro = mensagemErro(erro));
      }
    } finally {
      if (mounted) {
        setState(() => _ocupado = false);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final anuncio = widget.anuncio;
    final auth = ref.watch(authControllerProvider);
    final meu = anuncio.status != null;

    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        if (meu) ...[
          OutlinedButton.icon(
            onPressed: () => context.push('/anuncios/${anuncio.id}/editar'),
            icon: const Icon(Icons.edit_outlined),
            label: const Text('Editar anúncio'),
          ),
          const SizedBox(height: 8),
        ],
        FilledButton.icon(
          onPressed: _ocupado
              ? null
              : () => _correr(() async {
                    final numero = await ref.read(apiClientProvider).revelarTelefone(anuncio.id);
                    if (mounted) {
                      setState(() => _telefone = numero);
                    }
                  }),
          icon: const Icon(Icons.phone_outlined),
          label: Text(_telefone ?? 'Ver contacto'),
        ),
        if (_telefone != null)
          TextButton(
            onPressed: () => launchUrl(Uri.parse('tel:$_telefone')),
            child: Text('Ligar para $_telefone'),
          ),
        OutlinedButton.icon(
          onPressed: _ocupado
              ? null
              : () => _correr(() async {
                    final url = await ref.read(apiClientProvider).revelarWhatsapp(anuncio.id);
                    final uri = Uri.parse(url);
                    if (!await launchUrl(uri, mode: LaunchMode.externalApplication) && mounted) {
                      setState(() => _erro = 'Não foi possível abrir o WhatsApp.');
                    }
                  }),
          icon: const Icon(Icons.chat_outlined),
          label: const Text('WhatsApp'),
        ),
        const SizedBox(height: 16),
        Text('Mensagem', style: Theme.of(context).textTheme.titleMedium),
        const SizedBox(height: 8),
        TextField(
          controller: _mensagem,
          minLines: 2,
          maxLines: 4,
          decoration: const InputDecoration(hintText: 'Escreve ao anunciante'),
        ),
        const SizedBox(height: 8),
        FilledButton(
          onPressed: _ocupado
              ? null
              : () => _correr(() async {
                    if (!auth.autenticado) {
                      if (mounted) {
                        context.push('/login');
                      }
                      return;
                    }
                    await ref.read(apiClientProvider).enviarMensagem(anuncio.id, _mensagem.text.trim());
                    _mensagem.clear();
                    if (!context.mounted) {
                      return;
                    }
                    ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Mensagem enviada.')));
                  }),
          child: const Text('Enviar mensagem'),
        ),
        if (anuncio.reviews.isNotEmpty) ...[
          const SizedBox(height: 20),
          Text('Avaliações', style: Theme.of(context).textTheme.titleMedium),
          for (final review in anuncio.reviews)
            ListTile(
              contentPadding: EdgeInsets.zero,
              title: Text('${'★' * review.rating} ${review.autor ?? ''}'),
              subtitle: Text(review.comment ?? ''),
            ),
        ],
        if (auth.autenticado && !meu) ...[
          const SizedBox(height: 12),
          Text('A tua avaliação', style: Theme.of(context).textTheme.titleMedium),
          Row(
            children: [
              for (var i = 1; i <= 5; i++)
                IconButton(
                  onPressed: () => setState(() => _estrelas = i),
                  icon: Icon(i <= _estrelas ? Icons.star : Icons.star_border, color: TopCasaCores.verdeEscuro),
                ),
            ],
          ),
          TextField(
            controller: _comentario,
            decoration: const InputDecoration(hintText: 'Comentário (opcional)'),
          ),
          const SizedBox(height: 8),
          OutlinedButton(
            onPressed: _ocupado
                ? null
                : () => _correr(() async {
                      await ref.read(apiClientProvider).avaliar(anuncio.id, _estrelas, _comentario.text);
                      if (!context.mounted) {
                        return;
                      }
                      ScaffoldMessenger.of(context).showSnackBar(
                        const SnackBar(content: Text('Avaliação enviada. Fica pendente de aprovação.')),
                      );
                    }),
            child: const Text('Enviar avaliação'),
          ),
        ],
        if (_erro != null) ...[
          const SizedBox(height: 8),
          Text(_erro!, style: TextStyle(color: Theme.of(context).colorScheme.error)),
        ],
      ],
    );
  }
}

class _Selo extends StatelessWidget {
  const _Selo({required this.texto});

  final String texto;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
      decoration: BoxDecoration(
        color: const Color(0xFFDCFCE7),
        borderRadius: BorderRadius.circular(20),
      ),
      child: Text(texto, style: const TextStyle(color: Color(0xFF166534), fontWeight: FontWeight.w600, fontSize: 12)),
    );
  }
}
