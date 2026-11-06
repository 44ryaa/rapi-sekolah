import 'package:flutter/material.dart';
import '../models/event.dart';
import '../services/api_service.dart';
import '../theme/app_theme.dart';
import 'create_event_screen.dart';

class EventsScreen extends StatefulWidget {
  const EventsScreen({super.key});

  @override
  State<EventsScreen> createState() => _EventsScreenState();
}

class _EventsScreenState extends State<EventsScreen> {
  final _api = ApiService();
  List<EventModel> _events = [];
  bool _isLoading = true;
  String? _error;
  String _selectedFilter = 'Semua';

  final List<String> _filters = ['Semua', 'Diajukan', 'Disetujui', 'Ditolak'];

  @override
  void initState() {
    super.initState();
    _loadEvents();
  }

  Future<void> _loadEvents() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      String? status = _selectedFilter == 'Semua' ? null : _selectedFilter;
      final list = await _api.getEvents(status: status);
      setState(() {
        _events = list;
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _error = e.toString();
        _isLoading = false;
      });
    }
  }

  Color _getStatusColor(String status) {
    switch (status) {
      case 'Disetujui':
        return AppTheme.statusApproved;
      case 'Ditolak':
        return AppTheme.statusRejected;
      default:
        return AppTheme.statusPending;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Jadwal & Peminjaman'),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadEvents,
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton.extended(
        backgroundColor: AppTheme.primary,
        foregroundColor: Colors.white,
        icon: const Icon(Icons.add),
        label: const Text('Pinjam Ruang'),
        onPressed: () async {
          final res = await Navigator.push(
            context,
            MaterialPageRoute(builder: (_) => const CreateEventScreen()),
          );
          if (res == true) _loadEvents();
        },
      ),
      body: Column(
        children: [
          // Filter Chips
          SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
            child: Row(
              children: _filters.map((f) {
                final isSelected = _selectedFilter == f;
                return Padding(
                  padding: const EdgeInsets.only(right: 8),
                  child: FilterChip(
                    label: Text(f),
                    selected: isSelected,
                    onSelected: (val) {
                      setState(() => _selectedFilter = f);
                      _loadEvents();
                    },
                    selectedColor: AppTheme.primaryLight,
                    checkmarkColor: AppTheme.primary,
                    labelStyle: TextStyle(
                      color: isSelected ? AppTheme.primary : AppTheme.textMuted,
                      fontWeight: isSelected ? FontWeight.w600 : FontWeight.normal,
                    ),
                  ),
                );
              }).toList(),
            ),
          ),

          // Events List
          Expanded(
            child: _isLoading
                ? const Center(child: CircularProgressIndicator())
                : _error != null
                    ? Center(
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Text(_error!),
                            const SizedBox(height: 8),
                            ElevatedButton(
                              onPressed: _loadEvents,
                              child: const Text('Coba Lagi'),
                            ),
                          ],
                        ),
                      )
                    : _events.isEmpty
                        ? const Center(
                            child: Text(
                              'Belum ada agenda ruangan.',
                              style: TextStyle(color: AppTheme.textMuted),
                            ),
                          )
                        : RefreshIndicator(
                            onRefresh: _loadEvents,
                            child: ListView.separated(
                              padding: const EdgeInsets.all(16),
                              itemCount: _events.length,
                              separatorBuilder: (_, __) => const SizedBox(height: 12),
                              itemBuilder: (ctx, i) {
                                final ev = _events[i];
                                return Card(
                                  child: Padding(
                                    padding: const EdgeInsets.all(16),
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        Row(
                                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                          children: [
                                            Expanded(
                                              child: Text(
                                                ev.eventName,
                                                style: const TextStyle(
                                                  fontWeight: FontWeight.bold,
                                                  fontSize: 16,
                                                ),
                                              ),
                                            ),
                                            Container(
                                              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                              decoration: BoxDecoration(
                                                color: _getStatusColor(ev.status).withValues(alpha: 0.12),
                                                borderRadius: BorderRadius.circular(12),
                                              ),
                                              child: Text(
                                                ev.status,
                                                style: TextStyle(
                                                  color: _getStatusColor(ev.status),
                                                  fontWeight: FontWeight.bold,
                                                  fontSize: 11,
                                                ),
                                              ),
                                            ),
                                          ],
                                        ),
                                        const SizedBox(height: 8),
                                        Row(
                                          children: [
                                            const Icon(Icons.meeting_room_outlined, size: 16, color: AppTheme.textMuted),
                                            const SizedBox(width: 6),
                                            Text(
                                              ev.roomName ?? 'Ruang Sekolah',
                                              style: const TextStyle(fontSize: 13, color: AppTheme.textMuted),
                                            ),
                                          ],
                                        ),
                                        const SizedBox(height: 4),
                                        Row(
                                          children: [
                                            const Icon(Icons.access_time, size: 16, color: AppTheme.textMuted),
                                            const SizedBox(width: 6),
                                            Text(
                                              '${ev.date}  •  ${ev.startTime.substring(0, 5)} - ${ev.endTime.substring(0, 5)}',
                                              style: const TextStyle(fontSize: 13, color: AppTheme.textMuted),
                                            ),
                                          ],
                                        ),
                                        if (ev.requesterName != null) ...[
                                          const SizedBox(height: 4),
                                          Row(
                                            children: [
                                              const Icon(Icons.person_outline, size: 16, color: AppTheme.textMuted),
                                              const SizedBox(width: 6),
                                              Text(
                                                'Diajukan oleh: ${ev.requesterName}',
                                                style: const TextStyle(fontSize: 12, color: AppTheme.textMuted),
                                              ),
                                            ],
                                          ),
                                        ],
                                      ],
                                    ),
                                  ),
                                );
                              },
                            ),
                          ),
          ),
        ],
      ),
    );
  }
}
