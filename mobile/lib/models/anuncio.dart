class Anuncio {
  const Anuncio({
    required this.id,
    required this.slug,
    required this.titulo,
    required this.nomeProfissional,
    required this.imagens,
    required this.views,
    this.descricao,
    this.categoria,
    this.distrito,
    this.concelho,
    this.imagemCapa,
    this.certificacao = false,
    this.seguro = false,
    this.destacar = false,
    this.youtubeUrl,
    this.instagramUrl,
  });

  final int id;
  final String slug;
  final String titulo;
  final String? descricao;
  final String nomeProfissional;
  final String? categoria;
  final String? distrito;
  final String? concelho;
  final String? imagemCapa;
  final List<String> imagens;
  final int views;
  final bool certificacao;
  final bool seguro;
  final bool destacar;
  final String? youtubeUrl;
  final String? instagramUrl;

  String get localizacao {
    final partes = [concelho, distrito].whereType<String>().where((p) => p.isNotEmpty);
    return partes.join(', ');
  }

  factory Anuncio.fromJson(Map<String, dynamic> json) {
    final imagensJson = json['imagens'];
    final imagens = <String>[];
    if (imagensJson is List) {
      for (final item in imagensJson) {
        if (item is Map && item['url'] is String) {
          imagens.add(item['url'] as String);
        }
      }
    }

    String? nomeDe(Object? valor) {
      if (valor is Map && valor['nome'] is String) {
        return valor['nome'] as String;
      }
      return null;
    }

    return Anuncio(
      id: json['id'] as int,
      slug: json['slug'] as String? ?? '',
      titulo: json['titulo'] as String? ?? '',
      descricao: json['descricao'] as String?,
      nomeProfissional: json['nome_profissional'] as String? ?? '',
      categoria: nomeDe(json['categoria']),
      distrito: nomeDe(json['distrito']),
      concelho: nomeDe(json['concelho']),
      imagemCapa: json['imagem_capa'] as String?,
      imagens: imagens,
      views: json['views'] as int? ?? 0,
      certificacao: json['certificacao_profissional'] as bool? ?? false,
      seguro: json['seguro_responsabilidade_civil'] as bool? ?? false,
      destacar: json['destacar'] as bool? ?? false,
      youtubeUrl: json['youtube_url'] as String?,
      instagramUrl: json['instagram_url'] as String?,
    );
  }
}

class PaginaAnuncios {
  const PaginaAnuncios({
    required this.itens,
    required this.seed,
    required this.pagina,
    required this.ultimaPagina,
  });

  final List<Anuncio> itens;
  final int seed;
  final int pagina;
  final int ultimaPagina;

  bool get temMais => pagina < ultimaPagina;
}
