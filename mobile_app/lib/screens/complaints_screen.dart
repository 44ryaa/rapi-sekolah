import 'package:flutter/material.dart';
import '../models/complaint.dart';
import '../services/api_service.dart';
import '../theme/app_theme.dart';
import 'create_complaint_screen.dart';
import 'complaint_detail_screen.dart';

class ComplaintsScreen extends StatefulWidget {
  const ComplaintsScreen({super.key});

  @override
  State<ComplaintsScreen> createState() => _ComplaintsScreenState();
}

class _ComplaintsScreenState extends State<ComplaintsScreen> {
  final _api = ApiService();
  List<ComplaintModel> _complaints = [];
  bool _isLoading = true;
  String? _error;
  String _selectedFilter = 'Semua';

  final List<String> _filters = ['Semua', 'Diajukan', 'Diterima', 'Sudah Diperbaiki'];

  @override
  void initState() {
    super.initState();
    _loadComplaints();
  }

  Future<void> _loadComplaints() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      String? status;
      String? statusRepair;
      if (_selectedFilter == 'Diajukan' || _selectedFilter == 'Diterima') {
        status = _selectedFilter;
      } else if (_selectedFilter == 'Sudah Diperbaiki') {
        statusRepair = 'Sudah Diperbaiki';
      }

      final list = await _api.getComplaints(status: status, statusRepair: statusRepair);
      setState(() {
        _complaints = list;
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
      case 'Diterima':
      case 'Sudah Diperbaiki':
        return AppTheme.statusApproved;
      case 'Menunggu Spare Part':
        return AppTheme.statusRepairing;
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
        title: const Text('Pengaduan Fasilitas'),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadComplaints,
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton.extended(
        backgroundColor: AppTheme.primary,
        foregroundColor: Colors.white,
        icon: const Icon(Icons.add_a_photo),
        label: const Text('Lapor Kerusakan'),
        onPressed: () async {
          final res = await Navigator.push(
            context,
            MaterialPageRoute(builder: (_) => const CreateComplaintScreen()),
          );
          if (res == true) _loadComplaints();
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
                      _loadComplaints();
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

          // List Content
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
                              onPressed: _loadComplaints,
                              child: const Text('Coba Lagi'),
                            ),
                          ],
                        ),
                      )
                    : _complaints.isEmpty
                        ? const Center(
                            child: Text(
                              'Belum ada pengaduan fasilitas.',
                              style: TextStyle(color: AppTheme.textMuted),
                            ),
                          )
                        : RefreshIndicator(
                            onRefresh: _loadComplaints,
                            child: ListView.separated(
                              padding: const EdgeInsets.all(16),
                              itemCount: _complaints.length,
                              separatorBuilder: (_, __) => const SizedBox(height: 12),
                              itemBuilder: (ctx, i) {
                                final c = _complaints[i];
                                return Card(
                                  child: InkWell(
                                    borderRadius: BorderRadius.circular(16),
                                    onTap: () async {
                                      await Navigator.push(
                                        context,
                                        MaterialPageRoute(
                                          builder: (_) => ComplaintDetailScreen(complaintId: c.id),
                                        ),
                                      );
                                      _loadComplaints();
                                    },
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
                                                  c.roomName ?? 'Ruang Sekolah',
                                                  style: const TextStyle(
                                                    fontWeight: FontWeight.bold,
                                                    fontSize: 16,
                                                  ),
                                                ),
                                              ),
                                              Container(
                                                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                                decoration: BoxDecoration(
                                                  color: _getStatusColor(c.status).withValues(alpha: 0.12),
                                                  borderRadius: BorderRadius.circular(12),
                                                ),
                                                child: Text(
                                                  c.statusRepair ?? c.status,
                                                  style: TextStyle(
                                                    color: _getStatusColor(c.statusRepair ?? c.status),
                                                    fontWeight: FontWeight.bold,
                                                    fontSize: 11,
                                                  ),
                                                ),
                                              ),
                                            ],
                                          ),
                                          const SizedBox(height: 8),
                                          Text(
                                            c.description,
                                            maxLines: 2,
                                            overflow: TextOverflow.ellipsis,
                                            style: const TextStyle(
                                              fontSize: 14,
                                              color: AppTheme.textDark,
                                            ),
                                          ),
                                          const SizedBox(height: 12),
                                          Row(
                                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                            children: [
                                              Row(
                                                children: [
                                                  const Icon(Icons.person_outline, size: 14, color: AppTheme.textMuted),
                                                  const SizedBox(width: 4),
                                                  Text(
                                                    c.reporterName ?? '-',
                                                    style: const TextStyle(fontSize: 12, color: AppTheme.textMuted),
                                                  ),
                                                ],
                                              ),
                                              if (c.photoUrl != null)
                                                const Row(
                                                  children: [
                                                    Icon(Icons.photo_camera, size: 14, color: AppTheme.primary),
                                                    SizedBox(width: 4),
                                                    Text('Ada Foto', style: TextStyle(fontSize: 12, color: AppTheme.primary)),
                                                  ],
                                                ),
                                            ],
                                          ),
                                        ],
                                      ),
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
