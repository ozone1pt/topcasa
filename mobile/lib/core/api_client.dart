import 'dart:io';

import 'package:dio/dio.dart';
import 'package:topcasa/core/config.dart';
import 'package:topcasa/core/token_storage.dart';
import 'package:topcasa/models/anuncio.dart';
import 'package:topcasa/models/catalogo.dart';
import 'package:topcasa/models/filtro_anuncios.dart';
import 'package:topcasa/models/social.dart';
import 'package:topcasa/models/user.dart';

class ApiClient {
  ApiClient({Dio? dio, TokenStorage? storage})
      : _storage = storage ?? TokenStorage(),
        _dio = dio ??
            Dio(
              BaseOptions(
                baseUrl: AppConfig.baseUrlNormalizado,
                // O hCDN da Hostinger às vezes fica 25–60s pendurado.
                connectTimeout: const Duration(seconds: 30),
                receiveTimeout: const Duration(seconds: 90),
                sendTimeout: const Duration(seconds: 90),
                followRedirects: false,
                headers: {
                  'Accept': 'application/json',
                  'Content-Type': 'application/json',
                },
              ),
            ) {
    _dio.interceptors.add(
      InterceptorsWrapper(
        onRequest: (options, handler) {
          if (options.data is FormData) {
            options.headers.remove(Headers.contentTypeHeader);
          }
          final token = _token;
          if (token != null && token.isNotEmpty) {
            options.headers['Authorization'] = 'Bearer $token';
          }
          handler.next(options);
        },
        onError: (error, handler) async {
          if (await _repetir(error)) {
            try {
              final resposta = await _dio.fetch<dynamic>(error.requestOptions);
              handler.resolve(resposta);
              return;
            } on DioException catch (novo) {
              error = novo;
            }
          }
          final path = error.requestOptions.path;
          final emAuth = path.contains('auth/login') || path.contains('auth/register');
          if (error.response?.statusCode == 401 && !emAuth) {
            await limparToken();
            onSessaoInvalida?.call();
          }
          handler.next(error);
        },
      ),
    );
  }

  final Dio _dio;
  final TokenStorage _storage;
  String? _token;

  /// Chamado quando um token guardado deixa de ser aceite.
  void Function()? onSessaoInvalida;

  String? get token => _token;

  Future<void> carregarToken() async {
    _token = await _storage.ler();
  }

  Future<void> _gravarToken(String token) async {
    _token = token;
    await _storage.gravar(token);
  }

  Future<void> limparToken() async {
    _token = null;
    await _storage.limpar();
  }

  Future<Utilizador> login({
    required String login,
    required String password,
    String deviceName = 'android',
  }) async {
    final response = await _dio.post<Map<String, dynamic>>(
      'auth/login',
      data: {
        'login': login.trim(),
        'password': password,
        'device_name': deviceName,
      },
    );
    return _sessao(response.data!);
  }

  Future<Utilizador> registar({
    required String nome,
    required String email,
    required String password,
    required String passwordConfirmation,
    String deviceName = 'android',
  }) async {
    final response = await _dio.post<Map<String, dynamic>>(
      'auth/register',
      data: {
        'nome': nome.trim(),
        'email': email.trim().toLowerCase(),
        'password': password,
        'password_confirmation': passwordConfirmation,
        'device_name': deviceName,
      },
    );
    return _sessao(response.data!);
  }

  Future<String> recuperarPassword(String email) async {
    final response = await _dio.post<Map<String, dynamic>>(
      'auth/forgot-password',
      data: {'email': email.trim().toLowerCase()},
    );
    return response.data?['message'] as String? ?? 'Se a conta existir, enviámos o email.';
  }

  Future<void> logout() async {
    try {
      await _dio.post<void>('auth/logout');
    } finally {
      await limparToken();
    }
  }

  Future<Utilizador> eu() async {
    final response = await _dio.get<Map<String, dynamic>>('me');
    return Utilizador.fromJson(_mapa(response.data!['data']));
  }

  Future<Utilizador> atualizarPerfil(Map<String, dynamic> dados) async {
    final email = (dados['email'] as String?)?.trim().toLowerCase() ?? '';
    if (email.isEmpty) {
      throw DioException(
        requestOptions: RequestOptions(path: 'me'),
        message: 'O email é obrigatório.',
      );
    }
    dados['email'] = email;
    final response = await _dio.put<Map<String, dynamic>>('me', data: dados);
    return Utilizador.fromJson(_mapa(response.data!['data']));
  }

  Future<List<Distrito>> distritos() async {
    final response = await _dio.get<Map<String, dynamic>>('distritos');
    return _lista(response.data!['data']).map(Distrito.fromJson).toList();
  }

  Future<List<Concelho>> concelhos(int distritoId) async {
    final response = await _dio.get<Map<String, dynamic>>('distritos/$distritoId/concelhos');
    return _lista(response.data!['data']).map(Concelho.fromJson).toList();
  }

  Future<List<Categoria>> categorias() async {
    final response = await _dio.get<Map<String, dynamic>>('categorias');
    return _lista(response.data!['data']).map(Categoria.fromJson).toList();
  }

  Future<PaginaAnuncios> anuncios({
    required int pagina,
    int? seed,
    FiltroAnuncios filtro = const FiltroAnuncios(),
  }) async {
    final response = await _dio.get<Map<String, dynamic>>(
      'anuncios',
      queryParameters: {
        'page': pagina,
        'seed': ?seed,
        'categoria': ?filtro.categoriaId,
        'distrito': ?filtro.distritoId,
        'concelho_id': ?filtro.concelhoId,
        if (filtro.q.trim().isNotEmpty) 'q': filtro.q.trim(),
      },
    );
    final corpo = response.data!;
    final meta = _mapa(corpo['meta']);
    return PaginaAnuncios(
      itens: _lista(corpo['data']).map(Anuncio.fromJson).toList(),
      seed: corpo['seed'] as int,
      pagina: meta['current_page'] as int? ?? pagina,
      ultimaPagina: meta['last_page'] as int? ?? pagina,
    );
  }

  Future<Anuncio> anuncio(int id) async {
    final response = await _dio.get<Map<String, dynamic>>('anuncios/$id');
    return Anuncio.fromJson(_mapa(response.data!['data']));
  }

  Future<Utilizador> _sessao(Map<String, dynamic> corpo) async {
    await _gravarToken(corpo['token'] as String);
    return Utilizador.fromJson(_mapa(corpo['user']));
  }

  Future<bool> _repetir(DioException erro) async {
    final pedido = erro.requestOptions;
    final tentativa = pedido.extra['tentativa'] as int? ?? 0;
    if (tentativa >= 3 || !_deveRepetir(erro)) {
      return false;
    }
    pedido.extra['tentativa'] = tentativa + 1;
    await Future<void>.delayed(Duration(milliseconds: 700 * (1 << tentativa)));
    return true;
  }

  bool _deveRepetir(DioException erro) {
    if (_redirecionamentoParaOMesmoCaminho(erro)) {
      return true;
    }
    final metodo = erro.requestOptions.method.toUpperCase();
    if (metodo != 'GET' && metodo != 'HEAD') {
      return false;
    }
    final codigo = erro.response?.statusCode;
    return erro.type == DioExceptionType.connectionTimeout ||
        erro.type == DioExceptionType.receiveTimeout ||
        erro.type == DioExceptionType.sendTimeout ||
        erro.type == DioExceptionType.connectionError ||
        codigo == 502 ||
        codigo == 503 ||
        codigo == 504;
  }

  bool _redirecionamentoParaOMesmoCaminho(DioException erro) {
    final codigo = erro.response?.statusCode;
    if (codigo != 307 && codigo != 302 && codigo != 301) {
      return false;
    }
    final location = erro.response?.headers.value('location');
    if (location == null || location.isEmpty) {
      return false;
    }
    final destino = Uri.parse(location);
    final pedido = erro.requestOptions.uri;
    final caminhoDestino = destino.hasScheme ? destino.path : location.split('?').first;
    return caminhoDestino == pedido.path || caminhoDestino.endsWith(pedido.path);
  }

  Future<Anuncio> criarAnuncio(Map<String, dynamic> campos, List<File> fotos) async {
    final response = await _dio.post<Map<String, dynamic>>(
      'anuncios',
      data: await _formularioAnuncio(campos, fotos),
    );
    return Anuncio.fromJson(_mapa(response.data!['data']));
  }

  Future<Anuncio> atualizarAnuncio(int id, Map<String, dynamic> campos, List<File> fotos) async {
    // PHP leaves $_POST and $_FILES empty on a multipart PUT. Text-only edits
    // stay JSON PUT. New photos go as POST multipart, which Laravel accepts
    // on this route (and as PUT when _method is set).
    if (fotos.isEmpty) {
      final response = await _dio.put<Map<String, dynamic>>('anuncios/$id', data: campos);
      return Anuncio.fromJson(_mapa(response.data!['data']));
    }

    final dados = await _formularioAnuncio(campos, fotos);
    dados.fields.add(const MapEntry('_method', 'PUT'));
    final response = await _dio.post<Map<String, dynamic>>('anuncios/$id', data: dados);
    return Anuncio.fromJson(_mapa(response.data!['data']));
  }

  Future<void> apagarAnuncio(int id) => _dio.delete<void>('anuncios/$id');

  Future<Anuncio> reativarAnuncio(int id) async {
    final response = await _dio.post<Map<String, dynamic>>('anuncios/$id/reativar');
    return Anuncio.fromJson(_mapa(response.data!['data']));
  }

  Future<PaginaSimples<Anuncio>> meusAnuncios({int pagina = 1}) async {
    final response = await _dio.get<Map<String, dynamic>>('me/anuncios', queryParameters: {'page': pagina});
    return _pagina(response.data!, Anuncio.fromJson);
  }

  Future<bool> alternarFavorito(int id) async {
    final response = await _dio.post<Map<String, dynamic>>('anuncios/$id/favorito');
    return response.data?['favorito'] as bool? ?? false;
  }

  Future<List<Anuncio>> favoritos() async {
    final response = await _dio.get<Map<String, dynamic>>('favoritos');
    return _lista(response.data!['data']).map(Anuncio.fromJson).toList();
  }

  Future<String> revelarTelefone(int id) async {
    final response = await _dio.post<Map<String, dynamic>>('anuncios/$id/revelar-telefone');
    return response.data?['contacto'] as String? ?? '';
  }

  Future<String> revelarWhatsapp(int id) async {
    final response = await _dio.post<Map<String, dynamic>>('anuncios/$id/revelar-whatsapp');
    return response.data?['url'] as String? ?? '';
  }

  Future<void> enviarMensagem(int anuncioId, String texto) async {
    await _dio.post<void>('anuncios/$anuncioId/mensagens', data: {'mensagem': texto});
  }

  Future<({List<MensagemResumo> itens, int naoLidas})> mensagens({int pagina = 1}) async {
    final response = await _dio.get<Map<String, dynamic>>('mensagens', queryParameters: {'page': pagina});
    final corpo = response.data!;
    return (
      itens: _lista(corpo['data']).map(MensagemResumo.fromJson).toList(),
      naoLidas: corpo['nao_lidas'] as int? ?? 0,
    );
  }

  Future<MensagemResumo> mensagem(int id) async {
    final response = await _dio.get<Map<String, dynamic>>('mensagens/$id');
    return MensagemResumo.fromJson(_mapa(response.data!['data']));
  }

  Future<void> responderMensagem(int id, String texto) async {
    await _dio.post<void>('mensagens/$id/responder', data: {'mensagem': texto});
  }

  Future<void> apagarMensagem(int id) => _dio.delete<void>('mensagens/$id');

  Future<void> avaliar(int anuncioId, int rating, String? comentario) async {
    await _dio.post<void>('anuncios/$anuncioId/reviews', data: {
      'rating': rating,
      if (comentario != null && comentario.trim().isNotEmpty) 'comment': comentario.trim(),
    });
  }

  Future<({List<AlertaResumo> itens, int naoLidas})> alertas({int pagina = 1}) async {
    final response = await _dio.get<Map<String, dynamic>>('alertas', queryParameters: {'page': pagina});
    final corpo = response.data!;
    return (
      itens: _lista(corpo['data']).map(AlertaResumo.fromJson).toList(),
      naoLidas: corpo['nao_lidas'] as int? ?? 0,
    );
  }

  Future<void> marcarAlertaLido(int id) => _dio.post<void>('alertas/$id/lido');

  Future<void> apagarImagem(int id) => _dio.delete<void>('galeria/$id');

  Future<void> ordenarImagens(List<int> ids) => _dio.post<void>('galeria/ordenar', data: {'ids': ids});

  Future<String> rodarImagem(int id, String sentido) async {
    final response = await _dio.post<Map<String, dynamic>>('galeria/$id/rodar', data: {'sentido': sentido});
    return response.data?['url'] as String? ?? '';
  }

  Future<PaginaSimples<PedidoResumo>> orcamentos({int pagina = 1}) async {
    final response = await _dio.get<Map<String, dynamic>>('orcamentos', queryParameters: {'page': pagina});
    return _pagina(response.data!, PedidoResumo.fromJson);
  }

  Future<PaginaSimples<PedidoResumo>> meusOrcamentos({int pagina = 1}) async {
    final response = await _dio.get<Map<String, dynamic>>('me/orcamentos', queryParameters: {'page': pagina});
    return _pagina(response.data!, PedidoResumo.fromJson);
  }

  Future<PedidoResumo> criarOrcamento(Map<String, dynamic> campos, File? foto) async {
    final response = await _dio.post<Map<String, dynamic>>('orcamentos', data: await _formularioPedido(campos, foto));
    return PedidoResumo.fromJson(_mapa(response.data!['data']));
  }

  Future<Map<String, dynamic>> desbloquearOrcamento(int id) async {
    final response = await _dio.post<Map<String, dynamic>>('orcamentos/$id/desbloquear');
    return response.data ?? {};
  }

  Future<void> concluirOrcamento(int id) => _dio.post<void>('orcamentos/$id/concluir');

  Future<PaginaSimples<PedidoResumo>> recrutamento({int pagina = 1}) async {
    final response = await _dio.get<Map<String, dynamic>>('recrutamento', queryParameters: {'page': pagina});
    return _pagina(response.data!, PedidoResumo.fromJson);
  }

  Future<PaginaSimples<PedidoResumo>> meuRecrutamento({int pagina = 1}) async {
    final response = await _dio.get<Map<String, dynamic>>('me/recrutamento', queryParameters: {'page': pagina});
    return _pagina(response.data!, PedidoResumo.fromJson);
  }

  Future<PedidoResumo> criarRecrutamento(Map<String, dynamic> campos, File? foto) async {
    final response = await _dio.post<Map<String, dynamic>>('recrutamento', data: await _formularioPedido(campos, foto));
    return PedidoResumo.fromJson(_mapa(response.data!['data']));
  }

  Future<PedidoResumo> pausarRecrutamento(int id) async {
    final response = await _dio.post<Map<String, dynamic>>('recrutamento/$id/pausar');
    return PedidoResumo.fromJson(_mapa(response.data!['data']));
  }

  Future<void> apagarRecrutamento(int id) => _dio.delete<void>('recrutamento/$id');

  Future<ResumoConta> resumo() async {
    final response = await _dio.get<Map<String, dynamic>>('me/resumo');
    return ResumoConta.fromJson(_mapa(response.data!['data']));
  }

  Future<PaginaSimples<AvaliacaoRecebida>> reviewsRecebidas() async {
    final response = await _dio.get<Map<String, dynamic>>('me/reviews');
    return _pagina(response.data!, AvaliacaoRecebida.fromJson);
  }

  Future<void> moderarReview(int id, bool aprovar) =>
      _dio.post<void>('reviews/$id/${aprovar ? 'aprovar' : 'rejeitar'}');

  Future<void> marcarAlertasLidos() => _dio.post<void>('alertas/lidos');

  Future<void> alterarPassword(String atual, String nova) => _dio.put<void>('me/password', data: {
        'current_password': atual,
        'password': nova,
        'password_confirmation': nova,
      });

  Future<void> apagarConta(String password) => _dio.delete<void>('me', data: {'password': password});

  Future<PaginaSimples<Artigo>> blog({int pagina = 1}) async {
    final response = await _dio.get<Map<String, dynamic>>('blog', queryParameters: {'page': pagina});
    return _pagina(response.data!, Artigo.fromJson);
  }

  Future<Artigo> artigo(String slug) async {
    final response = await _dio.get<Map<String, dynamic>>('blog/$slug');
    return Artigo.fromJson(_mapa(response.data!['data']));
  }

  Future<void> subscreverNewsletter(String email, String? nome) => _dio.post<void>('newsletter', data: {
        'email': email.trim().toLowerCase(),
        if (nome != null && nome.trim().isNotEmpty) 'nome': nome.trim(),
      });

  Future<FormData> _formularioPedido(Map<String, dynamic> campos, File? foto) async {
    final dados = FormData.fromMap({
      for (final entrada in campos.entries)
        if (entrada.value != null) entrada.key: entrada.value,
    });
    if (foto != null) {
      dados.files.add(MapEntry('foto', await MultipartFile.fromFile(foto.path, filename: 'foto.jpg')));
    }
    return dados;
  }

  Future<FormData> _formularioAnuncio(Map<String, dynamic> campos, List<File> fotos) async {
    final dados = FormData.fromMap({
      for (final entrada in campos.entries)
        if (entrada.value != null) entrada.key: entrada.value,
    });
    for (final foto in fotos) {
      dados.files.add(MapEntry('imagens[]', await MultipartFile.fromFile(foto.path, filename: 'foto.jpg')));
    }
    return dados;
  }

  PaginaSimples<T> _pagina<T>(Map<String, dynamic> corpo, T Function(Map<String, dynamic>) ler) {
    final meta = corpo['meta'] is Map ? _mapa(corpo['meta']) : <String, dynamic>{};
    return PaginaSimples(
      itens: _lista(corpo['data']).map(ler).toList(),
      pagina: meta['current_page'] as int? ?? 1,
      ultima: meta['last_page'] as int? ?? 1,
    );
  }

  Map<String, dynamic> _mapa(Object? valor) => Map<String, dynamic>.from(valor as Map);

  List<Map<String, dynamic>> _lista(Object? valor) {
    return (valor as List).map((item) => Map<String, dynamic>.from(item as Map)).toList();
  }
}
