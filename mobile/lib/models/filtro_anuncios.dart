class FiltroAnuncios {
  const FiltroAnuncios({
    this.categoriaId,
    this.distritoId,
    this.concelhoId,
    this.q = '',
  });

  final int? categoriaId;
  final int? distritoId;
  final int? concelhoId;
  final String q;

  bool get ativo =>
      categoriaId != null || distritoId != null || concelhoId != null || q.trim().isNotEmpty;
}
