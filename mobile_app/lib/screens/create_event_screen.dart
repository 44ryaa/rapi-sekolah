import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../models/room.dart';
import '../services/api_service.dart';
import '../theme/app_theme.dart';

class CreateEventScreen extends StatefulWidget {
  final int? preselectedRoomId;

  const CreateEventScreen({super.key, this.preselectedRoomId});

  @override
  State<CreateEventScreen> createState() => _CreateEventScreenState();
}

class _CreateEventScreenState extends State<CreateEventScreen> {
  final _api = ApiService();
  final _eventNameController = TextEditingController();

  List<Room> _rooms = [];
  int? _selectedRoomId;
  DateTime _selectedDate = DateTime.now().add(const Duration(days: 1));
  bool _isMultiDay = false;
  DateTime? _endDate;
  int _sessionStart = 1;
  int _sessionEnd = 1;

  bool _isLoadingRooms = true;
  bool _isCheckingSlots = false;
  bool _isSubmitting = false;
  Map<int, dynamic> _slotsMap = {};

  final Map<int, String> _sessions = {
    1: 'Sesi 1 (07:00–08:30)',
    2: 'Sesi 2 (08:30–10:00)',
    3: 'Sesi 3 (10:15–11:45)',
    4: 'Sesi 4 (13:00–14:30)',
    5: 'Sesi 5 (14:30–16:00)',
    6: 'Sesi Penuh (07:00–16:00)',
  };

  @override
  void initState() {
    super.initState();
    _loadRooms();
  }

  @override
  void dispose() {
    _eventNameController.dispose();
    super.dispose();
  }

  Future<void> _loadRooms() async {
    try {
      final rooms = await _api.getRooms();
      setState(() {
        _rooms = rooms;
        _isLoadingRooms = false;
        if (widget.preselectedRoomId != null &&
            _rooms.any((r) => r.id == widget.preselectedRoomId)) {
          _selectedRoomId = widget.preselectedRoomId;
        } else if (_rooms.isNotEmpty) {
          _selectedRoomId = _rooms.first.id;
        }
      });
      if (_selectedRoomId != null) {
        _checkSlots();
      }
    } catch (e) {
      setState(() => _isLoadingRooms = false);
    }
  }

  Future<void> _checkSlots() async {
    if (_selectedRoomId == null) return;
    setState(() => _isCheckingSlots = true);

    final dateStr = DateFormat('yyyy-MM-dd').format(_selectedDate);
    try {
      final res = await _api.getEventSlots(_selectedRoomId!, dateStr);
      final rawSessions = res['sessions'];
      final Map<int, dynamic> parsed = {};
      if (rawSessions is Map) {
        rawSessions.forEach((k, v) {
          final intKey = int.tryParse(k.toString()) ?? 1;
          parsed[intKey] = v;
        });
      }
      setState(() {
        _slotsMap = parsed;
        _isCheckingSlots = false;
      });
    } catch (_) {
      setState(() => _isCheckingSlots = false);
    }
  }

  Future<void> _pickDate() async {
    final picked = await showDatePicker(
      context: context,
      initialDate: _selectedDate,
      firstDate: DateTime.now().add(const Duration(days: 1)),
      lastDate: DateTime.now().add(const Duration(days: 90)),
      selectableDayPredicate: (date) {
        // Exclude Saturday (6) and Sunday (7)
        return date.weekday != DateTime.saturday && date.weekday != DateTime.sunday;
      },
    );
    if (picked != null) {
      setState(() {
        _selectedDate = picked;
        if (_endDate != null && _endDate!.isBefore(_selectedDate)) {
          _endDate = _selectedDate.add(const Duration(days: 1));
        }
      });
      _checkSlots();
    }
  }

  Future<void> _pickEndDate() async {
    final initial = _endDate ?? _selectedDate.add(const Duration(days: 1));
    final picked = await showDatePicker(
      context: context,
      initialDate: initial.isBefore(_selectedDate) ? _selectedDate : initial,
      firstDate: _selectedDate,
      lastDate: _selectedDate.add(const Duration(days: 14)),
      selectableDayPredicate: (date) {
        return date.weekday != DateTime.saturday && date.weekday != DateTime.sunday;
      },
    );
    if (picked != null) {
      setState(() => _endDate = picked);
    }
  }

  Future<void> _handleSubmit() async {
    final name = _eventNameController.text.trim();
    if (name.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Masukkan nama kegiatan / agenda.')),
      );
      return;
    }

    if (_selectedRoomId == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Pilih ruangan.')),
      );
      return;
    }

    setState(() => _isSubmitting = true);
    final dateStr = DateFormat('yyyy-MM-dd').format(_selectedDate);
    final endDateStr = (_isMultiDay && _endDate != null)
        ? DateFormat('yyyy-MM-dd').format(_endDate!)
        : null;

    final res = await _api.createEvent(
      roomId: _selectedRoomId!,
      eventName: name,
      date: dateStr,
      endDate: endDateStr,
      sessionStart: _sessionStart,
      sessionEnd: _sessionStart == 6 ? null : _sessionEnd,
    );
    setState(() => _isSubmitting = false);

    if (res['success'] == true && mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Pengajuan pinjam ruangan berhasil dikirim!'),
          backgroundColor: AppTheme.statusApproved,
        ),
      );
      Navigator.pop(context, true);
    } else if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(res['message'] ?? 'Gagal mengajukan ruangan.'),
          backgroundColor: Colors.red.shade700,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Pinjam Ruangan')),
      body: _isLoadingRooms
          ? const Center(child: CircularProgressIndicator())
          : SingleChildScrollView(
              padding: const EdgeInsets.all(20),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  // Nama Acara
                  Card(
                    child: Padding(
                      padding: const EdgeInsets.all(16),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text(
                            'Nama Kegiatan / Acara',
                            style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                          ),
                          const SizedBox(height: 8),
                          TextField(
                            controller: _eventNameController,
                            decoration: const InputDecoration(
                              hintText: 'Contoh: Rapat Evaluasi Kurikulum Merdeka',
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(height: 16),

                  // Ruang & Tanggal
                  Card(
                    child: Padding(
                      padding: const EdgeInsets.all(16),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text(
                            'Pilih Ruangan',
                            style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                          ),
                          const SizedBox(height: 8),
                          DropdownButtonFormField<int>(
                            value: _selectedRoomId,
                            isExpanded: true,
                            items: _rooms.map((r) {
                              return DropdownMenuItem<int>(
                                value: r.id,
                                child: Text('${r.roomName} (${r.capacity ?? "-"} orang)'),
                              );
                            }).toList(),
                            onChanged: (val) {
                              setState(() => _selectedRoomId = val);
                              _checkSlots();
                            },
                          ),
                          const SizedBox(height: 16),
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Text(
                                _isMultiDay ? 'Tanggal Mulai' : 'Tanggal Peminjaman',
                                style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                              ),
                              Row(
                                children: [
                                  Switch(
                                    value: _isMultiDay,
                                    activeColor: AppTheme.ivory,
                                    activeTrackColor: AppTheme.bronze,
                                    onChanged: (val) {
                                      setState(() {
                                        _isMultiDay = val;
                                        if (val && _endDate == null) {
                                          _endDate = _selectedDate.add(const Duration(days: 1));
                                          if (_endDate!.weekday == DateTime.saturday) {
                                            _endDate = _endDate!.add(const Duration(days: 2));
                                          } else if (_endDate!.weekday == DateTime.sunday) {
                                            _endDate = _endDate!.add(const Duration(days: 1));
                                          }
                                        }
                                      });
                                    },
                                  ),
                                  const Text(
                                    'Multi-Hari',
                                    style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: AppTheme.ivory),
                                  ),
                                ],
                              ),
                            ],
                          ),
                          const SizedBox(height: 8),
                          InkWell(
                            onTap: _pickDate,
                            borderRadius: BorderRadius.circular(12),
                            child: InputDecorator(
                              decoration: const InputDecoration(
                                prefixIcon: Icon(Icons.calendar_today_outlined),
                              ),
                              child: Text(
                                DateFormat('EEEE, d MMMM yyyy', 'id_ID').format(_selectedDate),
                                style: const TextStyle(fontWeight: FontWeight.w600),
                              ),
                            ),
                          ),
                          if (_isMultiDay) ...[
                            const SizedBox(height: 16),
                            const Text(
                              'Tanggal Selesai (Hingga)',
                              style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                            ),
                            const SizedBox(height: 8),
                            InkWell(
                              onTap: _pickEndDate,
                              borderRadius: BorderRadius.circular(12),
                              child: InputDecorator(
                                decoration: const InputDecoration(
                                  prefixIcon: Icon(Icons.event_repeat_rounded),
                                ),
                                child: Text(
                                  _endDate != null
                                      ? DateFormat('EEEE, d MMMM yyyy', 'id_ID').format(_endDate!)
                                      : 'Pilih tanggal selesai',
                                  style: const TextStyle(fontWeight: FontWeight.w600),
                                ),
                              ),
                            ),
                            const SizedBox(height: 6),
                            const Text(
                              'Hari Sabtu & Minggu otomatis dilewati (maksimal 14 hari).',
                              style: TextStyle(fontSize: 11, color: AppTheme.textMuted),
                            ),
                          ],
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(height: 16),

                  // Sesi
                  Card(
                    child: Padding(
                      padding: const EdgeInsets.all(16),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text(
                            'Sesi Waktu',
                            style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                          ),
                          const SizedBox(height: 8),
                          DropdownButtonFormField<int>(
                            value: _sessionStart,
                            isExpanded: true,
                            items: _sessions.entries.map((e) {
                              final slot = _slotsMap[e.key];
                              final isTaken = slot != null && slot['taken'] == true;
                              return DropdownMenuItem<int>(
                                value: e.key,
                                enabled: !isTaken,
                                child: Text(
                                  isTaken ? '${e.value} (Terpakai)' : e.value,
                                  style: TextStyle(
                                    color: isTaken ? Colors.grey : AppTheme.textDark,
                                  ),
                                ),
                              );
                            }).toList(),
                            onChanged: (val) {
                              if (val != null) {
                                setState(() {
                                  _sessionStart = val;
                                  if (val > _sessionEnd && val != 6) {
                                    _sessionEnd = val;
                                  }
                                });
                              }
                            },
                          ),
                          if (_isCheckingSlots) ...[
                            const SizedBox(height: 8),
                            const Row(
                              children: [
                                SizedBox(
                                  width: 14,
                                  height: 14,
                                  child: CircularProgressIndicator(strokeWidth: 2),
                                ),
                                SizedBox(width: 8),
                                Text(
                                  'Memeriksa ketersediaan ruangan...',
                                  style: TextStyle(fontSize: 12, color: AppTheme.textMuted),
                                ),
                              ],
                            ),
                          ],
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(height: 24),

                  // Submit Button
                  ElevatedButton(
                    onPressed: _isSubmitting ? null : _handleSubmit,
                    child: _isSubmitting
                        ? const SizedBox(
                            height: 20,
                            width: 20,
                            child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                          )
                        : const Text('Ajukan Peminjaman'),
                  ),
                ],
              ),
            ),
    );
  }
}
