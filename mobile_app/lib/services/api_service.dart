import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;
import 'package:image_picker/image_picker.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../models/user.dart';
import '../models/room.dart';
import '../models/event.dart';
import '../models/complaint.dart';

class ApiService {
  static final ApiService _instance = ApiService._internal();
  factory ApiService() => _instance;
  ApiService._internal();

  static String get _defaultBaseUrl => 'https://rapi-sekolah.web.id';
  static const String _keyBaseUrl = 'api_base_url';
  static const String _keyToken = 'api_token';

  String _baseUrl = 'https://rapi-sekolah.web.id';
  String? _token;

  String get baseUrl => _baseUrl;
  String? get token => _token;

  Future<void> init() async {
    final prefs = await SharedPreferences.getInstance();
    final saved = prefs.getString(_keyBaseUrl);
    if (saved != null && saved.isNotEmpty) {
      if (kIsWeb && saved.contains('10.0.2.2')) {
        _baseUrl = 'http://localhost:8000';
      } else {
        _baseUrl = saved;
      }
    } else {
      _baseUrl = _defaultBaseUrl;
    }
    _token = prefs.getString(_keyToken);
  }

  Future<void> setBaseUrl(String newUrl) async {
    String cleanUrl = newUrl.trim();
    if (cleanUrl.endsWith('/')) {
      cleanUrl = cleanUrl.substring(0, cleanUrl.length - 1);
    }
    _baseUrl = cleanUrl;
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_keyBaseUrl, _baseUrl);
  }

  Future<void> saveToken(String token) async {
    _token = token;
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_keyToken, token);
  }

  Future<void> clearToken() async {
    _token = null;
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_keyToken);
  }

  Map<String, String> _headers({bool isJson = true}) {
    final headers = <String, String>{
      'Accept': 'application/json',
    };
    if (isJson) {
      headers['Content-Type'] = 'application/json';
    }
    if (_token != null && _token!.isNotEmpty) {
      headers['Authorization'] = 'Bearer $_token';
    }
    return headers;
  }

  // ── Auth ────────────────────────────────────────────────────────────────
  Future<Map<String, dynamic>> login(String username, String password) async {
    final url = Uri.parse('$_baseUrl/api/login');
    final response = await http.post(
      url,
      headers: _headers(),
      body: jsonEncode({
        'username': username,
        'password': password,
      }),
    );

    final data = jsonDecode(response.body);
    if (response.statusCode == 200) {
      final token = data['token'] as String;
      await saveToken(token);
      final user = User.fromJson(data['user']);
      return {'success': true, 'user': user};
    } else {
      return {
        'success': false,
        'message': data['message'] ?? 'Login gagal. Periksa username dan password.',
      };
    }
  }

  Future<void> logout() async {
    if (_token != null) {
      try {
        final url = Uri.parse('$_baseUrl/api/logout');
        await http.post(url, headers: _headers());
      } catch (_) {}
    }
    await clearToken();
  }

  Future<User?> getMe() async {
    if (_token == null) return null;
    final url = Uri.parse('$_baseUrl/api/me');
    try {
      final response = await http.get(url, headers: _headers());
      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        return User.fromJson(data['user']);
      }
    } catch (_) {}
    return null;
  }

  // ── Rooms ───────────────────────────────────────────────────────────────
  Future<List<Room>> getRooms() async {
    final url = Uri.parse('$_baseUrl/api/rooms?active=1');
    final response = await http.get(url, headers: _headers());
    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      final list = (data['data'] as List).map((e) => Room.fromJson(e)).toList();
      return list;
    }
    throw Exception('Gagal memuat daftar ruangan');
  }

  // ── Events ──────────────────────────────────────────────────────────────
  Future<List<EventModel>> getEvents({int? roomId, String? status}) async {
    final params = <String, String>{};
    if (roomId != null) params['room_id'] = roomId.toString();
    if (status != null && status.isNotEmpty) params['status'] = status;

    final url = Uri.parse('$_baseUrl/api/events').replace(queryParameters: params.isEmpty ? null : params);
    final response = await http.get(url, headers: _headers());
    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      return (data['data'] as List).map((e) => EventModel.fromJson(e)).toList();
    }
    throw Exception('Gagal memuat jadwal kegiatan');
  }

  Future<Map<String, dynamic>> getEventSlots(int roomId, String date) async {
    final url = Uri.parse('$_baseUrl/api/events/slots?room_id=$roomId&date=$date');
    final response = await http.get(url, headers: _headers());
    if (response.statusCode == 200) {
      return jsonDecode(response.body);
    }
    throw Exception('Gagal memeriksa ketersediaan sesi ruangan');
  }

  Future<Map<String, dynamic>> createEvent({
    required int roomId,
    required String eventName,
    required String date,
    String? endDate,
    required int sessionStart,
    int? sessionEnd,
  }) async {
    final url = Uri.parse('$_baseUrl/api/events');
    final body = <String, dynamic>{
      'room_id': roomId,
      'event_name': eventName,
      'date': date,
      if (endDate != null && endDate.isNotEmpty) 'end_date': endDate,
      'session_start': sessionStart,
      if (sessionEnd != null) 'session_end': sessionEnd,
    };
    final response = await http.post(
      url,
      headers: _headers(),
      body: jsonEncode(body),
    );

    final data = jsonDecode(response.body);
    if (response.statusCode == 201) {
      return {'success': true, 'message': data['message'] ?? 'Pengajuan berhasil'};
    } else {
      return {'success': false, 'message': data['message'] ?? 'Gagal mengajukan pinjam ruang'};
    }
  }

  // ── Complaints ──────────────────────────────────────────────────────────
  Future<List<ComplaintModel>> getComplaints({String? status, String? statusRepair, int? roomId}) async {
    final params = <String, String>{};
    if (status != null && status.isNotEmpty) params['status'] = status;
    if (statusRepair != null && statusRepair.isNotEmpty) params['status_repair'] = statusRepair;
    if (roomId != null) params['room_id'] = roomId.toString();

    final url = Uri.parse('$_baseUrl/api/complaints').replace(queryParameters: params.isEmpty ? null : params);
    final response = await http.get(url, headers: _headers());
    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      return (data['data'] as List).map((e) => ComplaintModel.fromJson(e)).toList();
    }
    throw Exception('Gagal memuat pengaduan');
  }

  Future<ComplaintModel> getComplaint(int id) async {
    final url = Uri.parse('$_baseUrl/api/complaints/$id');
    final response = await http.get(url, headers: _headers());
    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      return ComplaintModel.fromJson(data['data']);
    }
    throw Exception('Gagal memuat detail pengaduan');
  }

  Future<Map<String, dynamic>> createComplaint({
    required int roomId,
    required String description,
    XFile? photoFile,
  }) async {
    final url = Uri.parse('$_baseUrl/api/complaints');

    if (photoFile != null) {
      // Multipart upload
      final request = http.MultipartRequest('POST', url);
      request.headers.addAll(_headers(isJson: false));
      request.fields['room_id'] = roomId.toString();
      request.fields['description'] = description;

      final bytes = await photoFile.readAsBytes();
      final multipartFile = http.MultipartFile.fromBytes(
        'photo',
        bytes,
        filename: photoFile.name,
      );
      request.files.add(multipartFile);

      final streamedResponse = await request.send();
      final response = await http.Response.fromStream(streamedResponse);
      final data = jsonDecode(response.body);
      if (response.statusCode == 201) {
        return {'success': true, 'message': data['message'] ?? 'Laporan terkirim'};
      } else {
        return {'success': false, 'message': data['message'] ?? 'Gagal mengirim laporan'};
      }
    } else {
      // JSON upload
      final response = await http.post(
        url,
        headers: _headers(),
        body: jsonEncode({
          'room_id': roomId,
          'description': description,
        }),
      );
      final data = jsonDecode(response.body);
      if (response.statusCode == 201) {
        return {'success': true, 'message': data['message'] ?? 'Laporan terkirim'};
      } else {
        return {'success': false, 'message': data['message'] ?? 'Gagal mengirim laporan'};
      }
    }
  }

  Future<Map<String, dynamic>> updateComplaintStatus({
    required int id,
    required String statusRepair,
    String? repairNote,
  }) async {
    final url = Uri.parse('$_baseUrl/api/complaints/$id/status');
    final response = await http.patch(
      url,
      headers: _headers(),
      body: jsonEncode({
        'status_repair': statusRepair,
        if (repairNote != null) 'repair_note': repairNote,
      }),
    );
    final data = jsonDecode(response.body);
    if (response.statusCode == 200) {
      return {'success': true, 'message': data['message'] ?? 'Status berhasil diubah'};
    } else {
      return {'success': false, 'message': data['message'] ?? 'Gagal mengubah status'};
    }
  }

  // ── Reports ─────────────────────────────────────────────────────────────
  Future<Map<String, dynamic>> getReports({String period = 'monthly'}) async {
    final url = Uri.parse('$_baseUrl/api/reports?period=$period');
    final response = await http.get(url, headers: _headers());
    if (response.statusCode == 200) {
      return jsonDecode(response.body);
    }
    throw Exception('Gagal memuat rekap laporan');
  }
}
