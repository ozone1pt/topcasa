/// Base da API. Sobrepõe-se com `--dart-define=API_BASE_URL=...`.
class AppConfig {
  static const baseUrl = String.fromEnvironment(
    'API_BASE_URL',
    defaultValue: 'https://staging.topcasa.pt/api/v1',
  );

  static String get baseUrlNormalizado =>
      baseUrl.endsWith('/') ? baseUrl : '$baseUrl/';
}
