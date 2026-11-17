/// Base da API. Sobrepõe-se com `--dart-define=API_BASE_URL=...`.
class AppConfig {
  static const baseUrl = String.fromEnvironment(
    'API_BASE_URL',
    defaultValue: 'https://staging.topcasa.pt/api/v1',
  );

  static String get baseUrlNormalizado =>
      baseUrl.endsWith('/') ? baseUrl : '$baseUrl/';

  /// Site público, sem o prefixo /api/v1 (páginas legais e blog no browser).
  static String get siteUrl {
    final base = baseUrlNormalizado.replaceFirst(RegExp(r'api/v1/?$'), '');
    return base.endsWith('/') ? base.substring(0, base.length - 1) : base;
  }
}
