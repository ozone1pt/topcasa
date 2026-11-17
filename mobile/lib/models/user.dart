class Utilizador {
  const Utilizador({
    required this.id,
    required this.username,
    required this.nome,
    required this.email,
    this.apelido,
    this.telefone,
    this.nif,
    this.tipoConta,
    this.empresaNome,
    this.userType,
    this.status,
  });

  final int id;
  final String username;
  final String? nome;
  final String? apelido;
  final String? email;
  final String? telefone;
  final String? nif;
  final String? tipoConta;
  final String? empresaNome;
  final String? userType;
  final String? status;

  String get nomeApresentacao {
    final completo = '${nome ?? ''} ${apelido ?? ''}'.trim();
    return completo.isEmpty ? username : completo;
  }

  factory Utilizador.fromJson(Map<String, dynamic> json) {
    return Utilizador(
      id: json['id'] as int,
      username: json['username'] as String? ?? '',
      nome: json['nome'] as String?,
      apelido: json['apelido'] as String?,
      email: json['email'] as String?,
      telefone: json['telefone'] as String?,
      nif: json['nif'] as String?,
      tipoConta: json['tipo_conta'] as String?,
      empresaNome: json['empresa_nome'] as String?,
      userType: json['user_type'] as String?,
      status: json['status'] as String?,
    );
  }

  Map<String, dynamic> toPerfilJson() {
    return {
      'nome': nome ?? '',
      'apelido': apelido,
      'email': email ?? '',
      'telefone': telefone,
      'nif': nif,
      'tipo_conta': tipoConta,
      'empresa_nome': empresaNome,
    };
  }
}
