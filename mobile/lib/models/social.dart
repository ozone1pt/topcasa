class PaginaSimples<T> {
  const PaginaSimples({required this.itens, required this.pagina, required this.ultima});

  final List<T> itens;
  final int pagina;
  final int ultima;
}

class MensagemResumo {
  const MensagemResumo({
    required this.id,
    required this.mensagem,
    this.anuncioId,
    this.anuncioTitulo,
    this.remetenteNome,
    this.lida = false,
    this.respostas = const [],
  });

  final int id;
  final int? anuncioId;
  final String? anuncioTitulo;
  final String? remetenteNome;
  final String mensagem;
  final bool lida;
  final List<MensagemResumo> respostas;

  factory MensagemResumo.fromJson(Map<String, dynamic> json) {
    final respostas = json['respostas'];
    return MensagemResumo(
      id: json['id'] as int,
      anuncioId: json['anuncio_id'] as int?,
      anuncioTitulo: json['anuncio_titulo'] as String?,
      remetenteNome: json['remetente_nome'] as String?,
      mensagem: json['mensagem'] as String? ?? '',
      lida: json['lida'] as bool? ?? false,
      respostas: respostas is List
          ? respostas.whereType<Map>().map((item) => MensagemResumo.fromJson(Map<String, dynamic>.from(item))).toList()
          : const [],
    );
  }
}

class PedidoResumo {
  const PedidoResumo({
    required this.id,
    required this.titulo,
    this.descricao,
    this.distrito,
    this.fotoUrl,
    this.empresaNome,
    this.telefone,
    this.email,
    this.nome,
    this.status,
    this.motivoRejeicao,
  });

  final int id;
  final String titulo;
  final String? descricao;
  final String? distrito;
  final String? fotoUrl;
  final String? empresaNome;
  final String? telefone;
  final String? email;
  final String? nome;
  final String? status;
  final String? motivoRejeicao;

  factory PedidoResumo.fromJson(Map<String, dynamic> json) {
    final distrito = json['distrito'];
    return PedidoResumo(
      id: json['id'] as int,
      titulo: (json['titulo'] as String?)?.trim().isNotEmpty == true ? json['titulo'] as String : 'Pedido de orçamento',
      descricao: json['descricao'] as String?,
      distrito: distrito is Map ? distrito['nome'] as String? : null,
      fotoUrl: json['foto_url'] as String?,
      empresaNome: json['empresa_nome'] as String?,
      telefone: json['telefone'] as String?,
      email: json['email'] as String?,
      nome: json['nome'] as String?,
      status: json['status'] as String?,
      motivoRejeicao: json['motivo_rejeicao'] as String?,
    );
  }
}

class ResumoConta {
  const ResumoConta({
    required this.anuncios,
    required this.mensagensNaoLidas,
    required this.alertasNaoLidos,
    required this.favoritos,
    required this.reviewsPorModerar,
  });

  final Map<String, int> anuncios;
  final int mensagensNaoLidas;
  final int alertasNaoLidos;
  final int favoritos;
  final int reviewsPorModerar;

  factory ResumoConta.fromJson(Map<String, dynamic> json) {
    final anuncios = json['anuncios'];
    return ResumoConta(
      anuncios: anuncios is Map
          ? anuncios.map((chave, valor) => MapEntry(chave.toString(), (valor as num).toInt()))
          : const {},
      mensagensNaoLidas: json['mensagens_nao_lidas'] as int? ?? 0,
      alertasNaoLidos: json['alertas_nao_lidos'] as int? ?? 0,
      favoritos: json['favoritos'] as int? ?? 0,
      reviewsPorModerar: json['reviews_por_moderar'] as int? ?? 0,
    );
  }
}

class AvaliacaoRecebida {
  const AvaliacaoRecebida({
    required this.id,
    required this.rating,
    this.comment,
    this.status,
    this.autor,
    this.anuncioTitulo,
  });

  final int id;
  final int rating;
  final String? comment;
  final String? status;
  final String? autor;
  final String? anuncioTitulo;

  factory AvaliacaoRecebida.fromJson(Map<String, dynamic> json) {
    return AvaliacaoRecebida(
      id: json['id'] as int,
      rating: json['rating'] as int? ?? 0,
      comment: json['comment'] as String?,
      status: json['status'] as String?,
      autor: json['autor'] as String?,
      anuncioTitulo: json['anuncio_titulo'] as String?,
    );
  }
}

class Artigo {
  const Artigo({required this.slug, required this.titulo, this.conteudo, this.imagemUrl});

  final String slug;
  final String titulo;
  final String? conteudo;
  final String? imagemUrl;

  factory Artigo.fromJson(Map<String, dynamic> json) {
    return Artigo(
      slug: json['slug'] as String? ?? '',
      titulo: json['titulo'] as String? ?? '',
      conteudo: json['conteudo'] as String?,
      imagemUrl: json['imagem_url'] as String?,
    );
  }
}

class AlertaResumo {
  const AlertaResumo({
    required this.id,
    required this.titulo,
    required this.mensagem,
    this.tipo,
    this.lida = false,
  });

  final int id;
  final String titulo;
  final String mensagem;
  final String? tipo;
  final bool lida;

  factory AlertaResumo.fromJson(Map<String, dynamic> json) {
    return AlertaResumo(
      id: json['id'] as int,
      titulo: json['titulo'] as String? ?? '',
      mensagem: json['mensagem'] as String? ?? '',
      tipo: json['tipo'] as String?,
      lida: json['lida'] as bool? ?? false,
    );
  }
}
