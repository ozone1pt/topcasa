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
    this.status,
    this.motivoRejeicao,
    this.contacto,
    this.emailContacto,
    this.youtubeLink,
    this.instagramLink,
    this.categoriaId,
    this.distritoId,
    this.favorito = false,
    this.reviews = const [],
    this.galeria = const [],
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
  final String? status;
  final String? motivoRejeicao;
  final String? contacto;
  final String? emailContacto;
  final String? youtubeLink;
  final String? instagramLink;
  final int? categoriaId;
  final int? distritoId;
  final bool favorito;
  final List<Avaliacao> reviews;
  final List<FotoGaleria> galeria;

  String get localizacao {
    final partes = [concelho, distrito].whereType<String>().where((p) => p.isNotEmpty);
    return partes.join(', ');
  }

  factory Anuncio.fromJson(Map<String, dynamic> json) {
    final imagensJson = json['imagens'];
    final imagens = <String>[];
    final galeria = <FotoGaleria>[];
    if (imagensJson is List) {
      for (final item in imagensJson) {
        if (item is Map && item['url'] is String && item['id'] is int) {
          final foto = FotoGaleria(id: item['id'] as int, url: item['url'] as String);
          galeria.add(foto);
          imagens.add(foto.url);
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
      status: json['status'] as String?,
      motivoRejeicao: json['motivo_rejeicao'] as String?,
      contacto: json['contacto'] as String?,
      emailContacto: json['email'] as String?,
      youtubeLink: json['youtube_link'] as String?,
      instagramLink: json['instagram_link'] as String?,
      categoriaId: json['categoria_id'] as int?,
      distritoId: json['distrito_id'] as int?,
      favorito: json['favorito'] as bool? ?? false,
      reviews: json['reviews'] is List
          ? (json['reviews'] as List)
              .whereType<Map>()
              .map((item) => Avaliacao.fromJson(Map<String, dynamic>.from(item)))
              .toList()
          : const [],
      galeria: galeria,
    );
  }
}

class FotoGaleria {
  const FotoGaleria({required this.id, required this.url});

  final int id;
  final String url;
}

class Avaliacao {
  const Avaliacao({
    required this.id,
    required this.rating,
    this.comment,
    this.status,
    this.autor,
  });

  final int id;
  final int rating;
  final String? comment;
  final String? status;
  final String? autor;

  factory Avaliacao.fromJson(Map<String, dynamic> json) {
    return Avaliacao(
      id: json['id'] as int,
      rating: json['rating'] as int? ?? 0,
      comment: json['comment'] as String?,
      status: json['status'] as String?,
      autor: json['autor'] as String?,
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
