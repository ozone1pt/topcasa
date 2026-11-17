class Distrito {
  const Distrito({required this.id, required this.nome});

  final int id;
  final String nome;

  factory Distrito.fromJson(Map<String, dynamic> json) {
    return Distrito(id: json['id'] as int, nome: json['nome'] as String? ?? '');
  }
}

class Concelho {
  const Concelho({required this.id, required this.distritoId, required this.nome});

  final int id;
  final int distritoId;
  final String nome;

  factory Concelho.fromJson(Map<String, dynamic> json) {
    return Concelho(
      id: json['id'] as int,
      distritoId: json['distrito_id'] as int,
      nome: json['nome'] as String? ?? '',
    );
  }
}

class Categoria {
  const Categoria({required this.id, required this.nome, this.icone});

  final int id;
  final String nome;
  final String? icone;

  factory Categoria.fromJson(Map<String, dynamic> json) {
    return Categoria(
      id: json['id'] as int,
      nome: json['nome'] as String? ?? '',
      icone: json['icone'] as String?,
    );
  }
}
