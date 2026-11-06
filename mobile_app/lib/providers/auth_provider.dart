import 'package:flutter/material.dart';
import '../models/user.dart';
import '../services/api_service.dart';

class AuthProvider extends ChangeNotifier {
  final ApiService _api = ApiService();

  User? _currentUser;
  bool _isLoading = true;
  String? _errorMessage;

  User? get currentUser => _currentUser;
  bool get isLoading => _isLoading;
  bool get isAuthenticated => _currentUser != null;
  String? get errorMessage => _errorMessage;

  Future<void> checkAuth() async {
    _isLoading = true;
    notifyListeners();

    try {
      await _api.init();
      if (_api.token != null) {
        _currentUser = await _api.getMe().timeout(const Duration(seconds: 3));
      }
    } catch (_) {
      _currentUser = null;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<bool> login(String username, String password) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final res = await _api.login(username, password);
      if (res['success'] == true) {
        _currentUser = res['user'] as User;
        _isLoading = false;
        notifyListeners();
        return true;
      } else {
        _errorMessage = res['message'] as String;
        _isLoading = false;
        notifyListeners();
        return false;
      }
    } catch (e) {
      _errorMessage = 'Tidak dapat terhubung ke server ($e). Pastikan URL server benar dan server aktif.';
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  Future<void> logout() async {
    await _api.logout();
    _currentUser = null;
    notifyListeners();
  }

  Future<void> updateServerUrl(String url) async {
    await _api.setBaseUrl(url);
    notifyListeners();
  }
}
