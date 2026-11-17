import 'package:dio/dio.dart';
import 'package:topcasa/core/config.dart';
import 'package:topcasa/core/token_storage.dart';
import 'package:topcasa/models/anuncio.dart';
import 'package:topcasa/models/catalogo.dart';
import 'package:topcasa/models/filtro_anuncios.dart';
import 'package:topcasa/models/user.dart';

class ApiClient {
  ApiClient({Dio? dio, TokenStorage? storage})
      : _storage = storage ?? TokenStorage(),
        _dio = dio ??
            Dio(
              BaseOptions(
                baseUrl: AppConfig.baseUrlNormalizado,
                connectTimeout: const Duration(seconds: 15),
                receiveTimeout: const Duration(seconds: 20),
                headers: {
                  'Accept': 'application/json',
                  'Content-Type': 'application/json',
                },
              ),
            ) {
    _dio.interceptors.add(
      InterceptorsWrapper(
        onRequest: (options, handler) {
          final token = _token;
          if (token != null && token.isNotEmpty) {
            options.headers['Authorization'] = 'Bearer $token';
          }
          handler.next(options);
        },
        onError: (error, handler) async {
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

  Map<String, dynamic> _mapa(Object? valor) => Map<String, dynamic>.from(valor as Map);

  List<Map<String, dynamic>> _lista(Object? valor) {
    return (valor as List).map((item) => Map<String, dynamic>.from(item as Map)).toList();
  }
}
