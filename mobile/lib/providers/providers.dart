import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:topcasa/core/api_client.dart';
import 'package:topcasa/core/api_exception.dart';
import 'package:topcasa/models/anuncio.dart';
import 'package:topcasa/models/catalogo.dart';
import 'package:topcasa/models/filtro_anuncios.dart';
import 'package:topcasa/models/user.dart';

final apiClientProvider = Provider<ApiClient>((ref) => ApiClient());

class EstadoAuth {
  const EstadoAuth({this.utilizador, this.pronto = false});

  final Utilizador? utilizador;
  final bool pronto;

  bool get autenticado => utilizador != null;
}

class AuthController extends Notifier<EstadoAuth> {
  bool _aIniciar = false;

  @override
  EstadoAuth build() {
    ref.read(apiClientProvider).onSessaoInvalida = () {
      state = const EstadoAuth(pronto: true);
    };
    return const EstadoAuth();
  }

  Future<void> iniciar() async {
    if (_aIniciar || state.pronto) {
      return;
    }
    _aIniciar = true;
    final api = ref.read(apiClientProvider);
    await api.carregarToken();
    if (api.token == null || api.token!.isEmpty) {
      state = const EstadoAuth(pronto: true);
      return;
    }

    try {
      final utilizador = await api.eu();
      state = EstadoAuth(utilizador: utilizador, pronto: true);
    } catch (_) {
      await api.limparToken();
      state = const EstadoAuth(pronto: true);
    }
  }

  Future<void> login(String login, String password) async {
    final utilizador = await ref.read(apiClientProvider).login(login: login, password: password);
    state = EstadoAuth(utilizador: utilizador, pronto: true);
  }

  Future<void> registar({
    required String nome,
    required String email,
    required String password,
    required String confirmacao,
  }) async {
    final utilizador = await ref.read(apiClientProvider).registar(
          nome: nome,
          email: email,
          password: password,
          passwordConfirmation: confirmacao,
        );
    state = EstadoAuth(utilizador: utilizador, pronto: true);
  }

  Future<void> atualizar(Utilizador utilizador) async {
    state = EstadoAuth(utilizador: utilizador, pronto: true);
  }

  Future<void> terminarSessao() async {
    await ref.read(apiClientProvider).logout();
    state = const EstadoAuth(pronto: true);
  }
}

final authControllerProvider = NotifierProvider<AuthController, EstadoAuth>(AuthController.new);

final distritosProvider = FutureProvider<List<Distrito>>((ref) {
  return ref.watch(apiClientProvider).distritos();
});

final categoriasProvider = FutureProvider<List<Categoria>>((ref) {
  return ref.watch(apiClientProvider).categorias();
});

class EstadoAnuncios {
  const EstadoAnuncios({
    this.itens = const [],
    this.filtro = const FiltroAnuncios(),
    this.seed,
    this.pagina = 0,
    this.ultimaPagina = 0,
    this.aCarregar = false,
    this.aCarregarMais = false,
    this.erro,
  });

  final List<Anuncio> itens;
  final FiltroAnuncios filtro;
  final int? seed;
  final int pagina;
  final int ultimaPagina;
  final bool aCarregar;
  final bool aCarregarMais;
  final String? erro;

  bool get temMais => pagina < ultimaPagina;
  bool get vazio => !aCarregar && erro == null && itens.isEmpty;
}

class AnunciosController extends Notifier<EstadoAnuncios> {
  int _pedido = 0;

  @override
  EstadoAnuncios build() => const EstadoAnuncios();

  Future<void> aplicarFiltro(FiltroAnuncios filtro) async {
    state = EstadoAnuncios(filtro: filtro);
    await carregar(reiniciar: true);
  }

  Future<void> carregar({bool reiniciar = true}) async {
    final pedido = ++_pedido;
    final filtro = state.filtro;
    final seed = reiniciar ? null : state.seed;
    state = EstadoAnuncios(filtro: filtro, aCarregar: true);

    try {
      final pagina = await ref.read(apiClientProvider).anuncios(
            pagina: 1,
            seed: seed,
            filtro: filtro,
          );
      if (pedido != _pedido) {
        return;
      }
      state = EstadoAnuncios(
        itens: pagina.itens,
        filtro: filtro,
        seed: pagina.seed,
        pagina: pagina.pagina,
        ultimaPagina: pagina.ultimaPagina,
      );
    } catch (erro) {
      if (pedido != _pedido) {
        return;
      }
      state = EstadoAnuncios(filtro: filtro, erro: mensagemErro(erro));
    }
  }

  Future<void> carregarMais() async {
    if (state.aCarregar || state.aCarregarMais || !state.temMais || state.seed == null) {
      return;
    }
    final atual = state;
    final pedido = _pedido;
    state = EstadoAnuncios(
      itens: atual.itens,
      filtro: atual.filtro,
      seed: atual.seed,
      pagina: atual.pagina,
      ultimaPagina: atual.ultimaPagina,
      aCarregarMais: true,
    );

    try {
      final pagina = await ref.read(apiClientProvider).anuncios(
            pagina: atual.pagina + 1,
            seed: atual.seed,
            filtro: atual.filtro,
          );
      if (pedido != _pedido) {
        return;
      }
      state = EstadoAnuncios(
        itens: [...atual.itens, ...pagina.itens],
        filtro: atual.filtro,
        seed: pagina.seed,
        pagina: pagina.pagina,
        ultimaPagina: pagina.ultimaPagina,
      );
    } catch (erro) {
      if (pedido != _pedido) {
        return;
      }
      state = EstadoAnuncios(
        itens: atual.itens,
        filtro: atual.filtro,
        seed: atual.seed,
        pagina: atual.pagina,
        ultimaPagina: atual.ultimaPagina,
        erro: mensagemErro(erro),
      );
    }
  }
}

final anunciosControllerProvider =
    NotifierProvider<AnunciosController, EstadoAnuncios>(AnunciosController.new);

final anuncioDetalheProvider = FutureProvider.family<Anuncio, int>((ref, id) {
  return ref.watch(apiClientProvider).anuncio(id);
});
