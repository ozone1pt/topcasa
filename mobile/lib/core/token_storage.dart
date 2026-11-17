import 'package:flutter_secure_storage/flutter_secure_storage.dart';

class TokenStorage {
  TokenStorage({FlutterSecureStorage? storage})
      : _storage = storage ?? const FlutterSecureStorage();

  static const _chave = 'topcasa_token';

  final FlutterSecureStorage _storage;

  Future<String?> ler() => _storage.read(key: _chave);

  Future<void> gravar(String token) => _storage.write(key: _chave, value: token);

  Future<void> limpar() => _storage.delete(key: _chave);
}
