import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../models/complaint.dart';
import '../providers/auth_provider.dart';
import '../services/api_service.dart';
import '../theme/app_theme.dart';

class ComplaintDetailScreen extends StatefulWidget {
  final int complaintId;

  const ComplaintDetailScreen({super.key, required this.complaintId});

  @override
  State<ComplaintDetailScreen> createState() => _ComplaintDetailScreenState();
}

class _ComplaintDetailScreenState extends State<ComplaintDetailScreen> {
  final _api = ApiService();
  ComplaintModel? _complaint;
  bool _isLoading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _loadDetail();
  }

  Future<void> _loadDetail() async {
    try {
      final item = await _api.getComplaint(widget.complaintId);
      setState(() {
        _complaint = item;
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

  void _showUpdateStatusModal() {
    final noteController = TextEditingController(text: _complaint?.repairNote ?? '');
    String selectedStatus = _complaint?.statusRepair ?? 'Belum Diperbaiki';
    final statuses = ['Belum Diperbaiki', 'Menunggu Spare Part', 'Sudah Diperbaiki'];

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setModalState) => Padding(
          padding: EdgeInsets.only(
            left: 20,
            right: 20,
            top: 24,
            bottom: MediaQuery.of(ctx).viewInsets.bottom + 24,
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              const Text(
                'Perbarui Status Perbaikan',
                style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 16),
              DropdownButtonFormField<String>(
                value: selectedStatus,
                decoration: const InputDecoration(labelText: 'Status Perbaikan'),
                items: statuses.map((s) => DropdownMenuItem(value: s, child: Text(s))).toList(),
                onChanged: (val) {
                  if (val != null) setModalState(() => selectedStatus = val);
                },
              ),
              const SizedBox(height: 16),
              TextField(
                controller: noteController,
                maxLines: 3,
                decoration: const InputDecoration(
                  labelText: 'Catatan Perbaikan',
                  hintText: 'Tuliskan tindakan atau progres perbaikan...',
                ),
              ),
              const SizedBox(height: 20),
              ElevatedButton(
                onPressed: () async {
                  Navigator.pop(ctx);
                  final res = await _api.updateComplaintStatus(
                    id: widget.complaintId,
                    statusRepair: selectedStatus,
                    repairNote: noteController.text.trim(),
                  );
                  if (res['success'] == true && mounted) {
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(
                        content: Text('Status berhasil diperbarui!'),
                        backgroundColor: AppTheme.statusApproved,
                      ),
                    );
                    _loadDetail();
                  } else if (mounted) {
                    ScaffoldMessenger.of(context).showSnackBar(
                      SnackBar(content: Text(res['message'] ?? 'Gagal memperbarui status')),
                    );
                  }
                },
                child: const Text('Simpan Status'),
              ),
            ],
          ),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final user = Provider.of<AuthProvider>(context).currentUser;
    final canUpdate = user != null && (user.isAdmin || user.isPic);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Detail Pengaduan'),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? Center(child: Text(_error!))
              : _complaint == null
                  ? const Center(child: Text('Data tidak ditemukan'))
                  : SingleChildScrollView(
                      padding: const EdgeInsets.all(20),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.stretch,
                        children: [
                          // Status Header Card
                          Card(
                            child: Padding(
                              padding: const EdgeInsets.all(16),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Row(
                                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                    children: [
                                      Text(
                                        _complaint!.roomName ?? 'Ruangan Tidak Diketahui',
                                        style: const TextStyle(
                                          fontSize: 18,
                                          fontWeight: FontWeight.bold,
                                        ),
                                      ),
                                      Container(
                                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                        decoration: BoxDecoration(
                                          color: _getStatusColor(_complaint!.status).withValues(alpha: 0.12),
                                          borderRadius: BorderRadius.circular(20),
                                        ),
                                        child: Text(
                                          _complaint!.status,
                                          style: TextStyle(
                                            color: _getStatusColor(_complaint!.status),
                                            fontWeight: FontWeight.bold,
                                            fontSize: 12,
                                          ),
                                        ),
                                      ),
                                    ],
                                  ),
                                  const SizedBox(height: 8),
                                  if (_complaint!.statusRepair != null)
                                    Row(
                                      children: [
                                        const Text('Status Perbaikan: ', style: TextStyle(color: AppTheme.textMuted)),
                                        Text(
                                          _complaint!.statusRepair!,
                                          style: TextStyle(
                                            color: _getStatusColor(_complaint!.statusRepair!),
                                            fontWeight: FontWeight.w600,
                                          ),
                                        ),
                                      ],
                                    ),
                                ],
                              ),
                            ),
                          ),
                          const SizedBox(height: 16),

                          // Foto jika ada
                          if (_complaint!.photoUrl != null && _complaint!.photoUrl!.isNotEmpty) ...[
                            ClipRRect(
                              borderRadius: BorderRadius.circular(16),
                              child: Image.network(
                                _complaint!.photoUrl!,
                                fit: BoxFit.cover,
                                errorBuilder: (ctx, err, stack) => Container(
                                  height: 180,
                                  color: Colors.grey.shade200,
                                  child: const Center(
                                    child: Icon(Icons.broken_image, size: 48, color: Colors.grey),
                                  ),
                                ),
                              ),
                            ),
                            const SizedBox(height: 16),
                          ],

                          // Deskripsi
                          Card(
                            child: Padding(
                              padding: const EdgeInsets.all(16),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  const Text(
                                    'Deskripsi Kerusakan',
                                    style: TextStyle(
                                      fontWeight: FontWeight.bold,
                                      color: AppTheme.textMuted,
                                      fontSize: 13,
                                    ),
                                  ),
                                  const SizedBox(height: 6),
                                  Text(
                                    _complaint!.description,
                                    style: const TextStyle(fontSize: 15, height: 1.4),
                                  ),
                                  if (_complaint!.repairNote != null && _complaint!.repairNote!.isNotEmpty) ...[
                                    const Divider(height: 24),
                                    const Text(
                                      'Catatan Penanganan / Perbaikan',
                                      style: TextStyle(
                                        fontWeight: FontWeight.bold,
                                        color: AppTheme.textMuted,
                                        fontSize: 13,
                                      ),
                                    ),
                                    const SizedBox(height: 6),
                                    Text(
                                      _complaint!.repairNote!,
                                      style: const TextStyle(fontSize: 14, color: AppTheme.statusRepairing),
                                    ),
                                  ],
                                ],
                              ),
                            ),
                          ),
                          const SizedBox(height: 16),

                          // Info Pelapor & Waktu
                          Card(
                            child: Padding(
                              padding: const EdgeInsets.all(16),
                              child: Column(
                                children: [
                                  Row(
                                    children: [
                                      const Icon(Icons.person_outline, size: 20, color: AppTheme.textMuted),
                                      const SizedBox(width: 8),
                                      const Text('Pelapor: ', style: TextStyle(color: AppTheme.textMuted)),
                                      Text(
                                        _complaint!.reporterName ?? '-',
                                        style: const TextStyle(fontWeight: FontWeight.w600),
                                      ),
                                    ],
                                  ),
                                  const SizedBox(height: 8),
                                  Row(
                                    children: [
                                      const Icon(Icons.access_time, size: 20, color: AppTheme.textMuted),
                                      const SizedBox(width: 8),
                                      const Text('Dilaporkan: ', style: TextStyle(color: AppTheme.textMuted)),
                                      Text(
                                        _complaint!.reportedAt != null
                                            ? _complaint!.reportedAt!.substring(0, 10)
                                            : '-',
                                        style: const TextStyle(fontWeight: FontWeight.w600),
                                      ),
                                    ],
                                  ),
                                ],
                              ),
                            ),
                          ),
                          const SizedBox(height: 24),

                          // Tombol update status untuk PIC/Admin
                          if (canUpdate)
                            ElevatedButton.icon(
                              onPressed: _showUpdateStatusModal,
                              icon: const Icon(Icons.edit_note),
                              label: const Text('Update Status Perbaikan'),
                            ),
                        ],
                      ),
                    ),
    );
  }
}
