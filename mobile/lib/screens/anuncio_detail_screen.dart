import 'package:material_ui/material_ui.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:topcasa/core/theme.dart';
import 'package:topcasa/models/anuncio.dart';
import 'package:topcasa/providers/providers.dart';

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
            ],
          ),
        ),
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
