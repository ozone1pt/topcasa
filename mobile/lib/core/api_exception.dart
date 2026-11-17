import 'package:dio/dio.dart';

/// Mensagem legível a partir de um 422 do Laravel ou de uma falha de rede.
String mensagemErro(Object erro) {
  if (erro is DioException) {
    final data = erro.response?.data;
    if (data is Map) {
      final errors = data['errors'];
      if (errors is Map) {
        final linhas = <String>[];
        for (final valor in errors.values) {
          if (valor is List) {
            linhas.addAll(valor.map((item) => item.toString()));
          } else if (valor != null) {
            linhas.add(valor.toString());
          }
        }
        if (linhas.isNotEmpty) {
          return linhas.join('\n');
        }
      }
      final message = data['message'];
      if (message is String && message.isNotEmpty) {
        return message;
      }
    }

    if (erro.type == DioExceptionType.connectionTimeout ||
        erro.type == DioExceptionType.receiveTimeout ||
        erro.type == DioExceptionType.sendTimeout ||
        erro.type == DioExceptionType.connectionError) {
      return 'A ligação ao servidor demorou demasiado. Tenta outra vez daqui a pouco.';
    }
  }

  return 'Não foi possível concluir o pedido.';
}
