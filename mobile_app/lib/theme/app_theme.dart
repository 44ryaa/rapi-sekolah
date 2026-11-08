import 'package:flutter/material.dart';

class AppTheme {
  // ── Luxury Espresso & Ivory Palette ───────────────────────────────────────
  // Exact brand colors: #000000 | #1F150C | #412D15 | #E1DCC9
  static const Color black = Color(0xFF000000);
  static const Color espresso = Color(0xFF1F150C);
  static const Color espressoDark = Color(0xFF150E08);
  static const Color bronze = Color(0xFF412D15);
  static const Color bronzeLight = Color(0xFF5A4022);
  static const Color bronzeSoft = Color(0xFF2E2012);
  static const Color ivory = Color(0xFFE1DCC9);
  static const Color ivoryLight = Color(0xFFF5F2EA);

  static const Color primary = Color(0xFFE1DCC9); // Warm Ivory
  static const Color primaryDark = Color(0xFF412D15); // Rich Bronze
  static const Color primaryLight = Color(0xFF2E2012); // Deep Roast Soft
  static const Color accent = Color(0xFF412D15); // Bronze Umber
  static const Color background = Color(0xFF080604); // Deep Obsidian Black
  static const Color surface = Color(0xFF1F150C); // Espresso Card
  static const Color surfaceHover = Color(0xFF291D12);
  static const Color textDark = Color(0xFFF5F2EA); // Crisp Ivory Text
  static const Color textMuted = Color(0xFFA89F91); // Warm Muted Tan
  static const Color border = Color(0xFF412D15); // Bronze Border

  // Status colors
  static const Color statusPending = Color(0xFFD97706);
  static const Color statusApproved = Color(0xFF10B981);
  static const Color statusRejected = Color(0xFFEF4444);
  static const Color statusRepairing = Color(0xFF3B82F6);

  static ThemeData get darkTheme {
    return ThemeData(
      useMaterial3: true,
      brightness: Brightness.dark,
      colorScheme: const ColorScheme.dark(
        primary: primary,
        secondary: bronzeLight,
        surface: surface,
        onSurface: textDark,
        onPrimary: espresso,
        outline: border,
      ),
      scaffoldBackgroundColor: background,
      appBarTheme: const AppBarTheme(
        backgroundColor: surface,
        elevation: 0,
        centerTitle: false,
        iconTheme: IconThemeData(color: textDark),
        titleTextStyle: TextStyle(
          color: textDark,
          fontSize: 18,
          fontWeight: FontWeight.w700,
          letterSpacing: -0.3,
        ),
      ),
      cardTheme: CardThemeData(
        color: surface,
        elevation: 0,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(16),
          side: const BorderSide(color: border, width: 1),
        ),
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: espressoDark,
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: border),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: border),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: primary, width: 1.5),
        ),
        labelStyle: const TextStyle(color: textMuted),
        hintStyle: const TextStyle(color: textMuted),
        prefixIconColor: textMuted,
        suffixIconColor: textMuted,
      ),
      elevatedButtonTheme: ElevatedButtonThemeData(
        style: ElevatedButton.styleFrom(
          backgroundColor: primary,
          foregroundColor: espresso,
          elevation: 0,
          padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 14),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(12),
          ),
          textStyle: const TextStyle(
            fontSize: 15,
            fontWeight: FontWeight.w700,
          ),
        ),
      ),
      navigationBarTheme: NavigationBarThemeData(
        backgroundColor: surface,
        indicatorColor: bronzeLight,
        iconTheme: WidgetStateProperty.resolveWith((states) {
          if (states.contains(WidgetState.selected)) {
            return const IconThemeData(color: primary);
          }
          return const IconThemeData(color: textMuted);
        }),
        labelTextStyle: WidgetStateProperty.resolveWith((states) {
          if (states.contains(WidgetState.selected)) {
            return const TextStyle(
              fontSize: 12,
              fontWeight: FontWeight.w700,
              color: primary,
            );
          }
          return const TextStyle(
            fontSize: 12,
            fontWeight: FontWeight.w500,
            color: textMuted,
          );
        }),
      ),
      dialogTheme: DialogThemeData(
        backgroundColor: surface,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(16),
          side: const BorderSide(color: border),
        ),
        titleTextStyle: const TextStyle(
          color: textDark,
          fontSize: 18,
          fontWeight: FontWeight.bold,
        ),
        contentTextStyle: const TextStyle(
          color: textMuted,
          fontSize: 14,
        ),
      ),
      bottomSheetTheme: const BottomSheetThemeData(
        backgroundColor: surface,
        modalBackgroundColor: surface,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
          side: BorderSide(color: border),
        ),
      ),
    );
  }

  static ThemeData get lightTheme => darkTheme;
}
