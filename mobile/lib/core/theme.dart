import 'package:material_ui/material_ui.dart';

/// Cores da identidade TopCasa (navy / verde), as mesmas do Tailwind do site:
/// slate-900 #0F172A, slate-500 #64748B, slate-100 #F1F5F9, green-500 #22C55E.
class TopCasaCores {
  static const verde = Color(0xFF22C55E);
  static const verdeEscuro = Color(0xFF16A34A);
  static const navy = Color(0xFF0F172A);
  static const textoSuave = Color(0xFF64748B);
  static const fundo = Color(0xFFF1F5F9);
  static const linha = Color(0xFFE2E8F0);
}

ThemeData temaTopCasa() {
  final base = ThemeData(
    useMaterial3: true,
    brightness: Brightness.light,
    colorScheme: ColorScheme.fromSeed(
      seedColor: TopCasaCores.verde,
      primary: TopCasaCores.verde,
      onPrimary: Colors.white,
      secondary: TopCasaCores.navy,
      onSecondary: Colors.white,
      surface: Colors.white,
    ),
    scaffoldBackgroundColor: TopCasaCores.fundo,
  );

  const montserrat = TextStyle(fontFamily: 'Montserrat');
  final texto = base.textTheme.apply(
    fontFamily: 'Montserrat',
    bodyColor: TopCasaCores.navy,
    displayColor: TopCasaCores.navy,
  );

  return base.copyWith(
    textTheme: texto,
    appBarTheme: AppBarTheme(
      backgroundColor: TopCasaCores.navy,
      foregroundColor: Colors.white,
      elevation: 0,
      centerTitle: false,
      titleTextStyle: montserrat.copyWith(
        color: Colors.white,
        fontWeight: FontWeight.w700,
        fontSize: 18,
      ),
    ),
    filledButtonTheme: FilledButtonThemeData(
      style: FilledButton.styleFrom(
        backgroundColor: TopCasaCores.verde,
        foregroundColor: Colors.white,
        minimumSize: const Size.fromHeight(48),
        textStyle: montserrat.copyWith(fontWeight: FontWeight.w700),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      ),
    ),
    outlinedButtonTheme: OutlinedButtonThemeData(
      style: OutlinedButton.styleFrom(
        foregroundColor: TopCasaCores.navy,
        minimumSize: const Size.fromHeight(48),
        side: const BorderSide(color: TopCasaCores.linha),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      ),
    ),
    inputDecorationTheme: InputDecorationTheme(
      filled: true,
      fillColor: Colors.white,
      labelStyle: montserrat.copyWith(color: TopCasaCores.textoSuave),
      border: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
        borderSide: const BorderSide(color: TopCasaCores.linha),
      ),
      enabledBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
        borderSide: const BorderSide(color: TopCasaCores.linha),
      ),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
        borderSide: const BorderSide(color: TopCasaCores.verde, width: 1.6),
      ),
    ),
    cardTheme: CardThemeData(
      color: Colors.white,
      elevation: 0,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(16),
        side: const BorderSide(color: TopCasaCores.linha),
      ),
    ),
  );
}
