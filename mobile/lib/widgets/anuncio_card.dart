import 'package:material_ui/material_ui.dart';
import 'package:topcasa/core/theme.dart';
import 'package:topcasa/models/anuncio.dart';

class AnuncioCard extends StatelessWidget {
  const AnuncioCard({super.key, required this.anuncio, required this.onTap});

  final Anuncio anuncio;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Card(
      clipBehavior: Clip.antiAlias,
      margin: const EdgeInsets.only(bottom: 12),
      child: InkWell(
        onTap: onTap,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            AspectRatio(
              aspectRatio: 16 / 9,
              child: anuncio.imagemCapa == null
                  ? const ColoredBox(
                      color: TopCasaCores.fundo,
                      child: Icon(Icons.home_repair_service_outlined, size: 42, color: TopCasaCores.textoSuave),
                    )
                  : Image.network(
                      anuncio.imagemCapa!,
                      fit: BoxFit.cover,
                      errorBuilder: (_, _, _) => const ColoredBox(
                        color: TopCasaCores.fundo,
                        child: Icon(Icons.broken_image_outlined, color: TopCasaCores.textoSuave),
                      ),
                    ),
            ),
            Padding(
              padding: const EdgeInsets.all(14),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  if (anuncio.categoria != null)
                    Text(
                      anuncio.categoria!.toUpperCase(),
                      style: const TextStyle(
                        color: TopCasaCores.verdeEscuro,
                        fontSize: 11,
                        fontWeight: FontWeight.w700,
                        letterSpacing: 0.6,
                      ),
                    ),
                  const SizedBox(height: 4),
                  Text(
                    anuncio.titulo,
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 16),
                  ),
                  if (anuncio.nomeProfissional.isNotEmpty) ...[
                    const SizedBox(height: 4),
                    Text(
                      anuncio.nomeProfissional,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(color: TopCasaCores.textoSuave),
                    ),
                  ],
                  if (anuncio.localizacao.isNotEmpty) ...[
                    const SizedBox(height: 6),
                    Row(
                      children: [
                        const Icon(Icons.place_outlined, size: 16, color: TopCasaCores.textoSuave),
                        const SizedBox(width: 4),
                        Expanded(
                          child: Text(
                            anuncio.localizacao,
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                            style: const TextStyle(color: TopCasaCores.textoSuave),
                          ),
                        ),
                      ],
                    ),
                  ],
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}
