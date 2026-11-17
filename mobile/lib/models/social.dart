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
