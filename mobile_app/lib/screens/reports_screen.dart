import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../theme/app_theme.dart';

class ReportsScreen extends StatefulWidget {
  const ReportsScreen({super.key});

  @override
  State<ReportsScreen> createState() => _ReportsScreenState();
}

class _ReportsScreenState extends State<ReportsScreen> {
  final _api = ApiService();
  bool _isLoading = true;
  String? _error;
  String _period = 'monthly';
  Map<String, dynamic>? _reportData;

  @override
  void initState() {
    super.initState();
    _loadReport();
  }

  Future<void> _loadReport() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      final res = await _api.getReports(period: _period);
      setState(() {
        _reportData = res;
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _error = e.toString();
        _isLoading = false;
      });
    }
  }

  Widget _buildStatCard(String label, String value, IconData icon, Color color) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  label,
                  style: const TextStyle(fontSize: 12, color: AppTheme.textMuted, fontWeight: FontWeight.w600),
                ),
                Icon(icon, size: 20, color: color),
              ],
            ),
            const SizedBox(height: 12),
            Text(
              value,
              style: TextStyle(
                fontSize: 24,
                fontWeight: FontWeight.bold,
                color: color,
              ),
            ),
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final stats = _reportData?['stats'] as Map<String, dynamic>?;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Rekap & Laporan'),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadReport,
          ),
        ],
      ),
      body: Column(
        children: [
          // Period Selector
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
            child: SegmentedButton<String>(
              segments: const [
                ButtonSegment(value: 'daily', label: Text('Harian')),
                ButtonSegment(value: 'weekly', label: Text('Mingguan')),
                ButtonSegment(value: 'monthly', label: Text('Bulanan')),
              ],
              selected: {_period},
              onSelectionChanged: (val) {
                setState(() => _period = val.first);
                _loadReport();
              },
            ),
          ),

          // Content
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
                              onPressed: _loadReport,
                              child: const Text('Coba Lagi'),
                            ),
                          ],
                        ),
                      )
                    : RefreshIndicator(
                        onRefresh: _loadReport,
                        child: SingleChildScrollView(
                          padding: const EdgeInsets.all(16),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                _reportData?['period_label'] ?? 'Laporan',
                                style: const TextStyle(
                                  fontSize: 16,
                                  fontWeight: FontWeight.bold,
                                  color: AppTheme.textDark,
                                ),
                              ),
                              const SizedBox(height: 16),

                              // Header Laporan Agenda
                              const Text(
                                'Peminjaman Ruangan',
                                style: TextStyle(
                                  fontSize: 14,
                                  fontWeight: FontWeight.bold,
                                  color: AppTheme.textMuted,
                                ),
                              ),
                              const SizedBox(height: 8),
                              GridView.count(
                                crossAxisCount: 2,
                                shrinkWrap: true,
                                physics: const NeverScrollableScrollPhysics(),
                                crossAxisSpacing: 12,
                                mainAxisSpacing: 12,
                                childAspectRatio: 1.4,
                                children: [
                                  _buildStatCard(
                                    'Total Agenda',
                                    '${stats?['total_events'] ?? 0}',
                                    Icons.calendar_month,
                                    AppTheme.primary,
                                  ),
                                  _buildStatCard(
                                    'Disetujui',
                                    '${stats?['approved_events'] ?? 0}',
                                    Icons.check_circle_outline,
                                    AppTheme.statusApproved,
                                  ),
                                  _buildStatCard(
                                    'Diajukan',
                                    '${stats?['pending_events'] ?? 0}',
                                    Icons.hourglass_top,
                                    AppTheme.statusPending,
                                  ),
                                  _buildStatCard(
                                    'Ditolak',
                                    '${stats?['rejected_events'] ?? 0}',
                                    Icons.cancel_outlined,
                                    AppTheme.statusRejected,
                                  ),
                                ],
                              ),
                              const SizedBox(height: 24),

                              // Header Pengaduan
                              const Text(
                                'Pengaduan Sarana Prasarana',
                                style: TextStyle(
                                  fontSize: 14,
                                  fontWeight: FontWeight.bold,
                                  color: AppTheme.textMuted,
                                ),
                              ),
                              const SizedBox(height: 8),
                              GridView.count(
                                crossAxisCount: 2,
                                shrinkWrap: true,
                                physics: const NeverScrollableScrollPhysics(),
                                crossAxisSpacing: 12,
                                mainAxisSpacing: 12,
                                childAspectRatio: 1.4,
                                children: [
                                  _buildStatCard(
                                    'Total Aduan',
                                    '${stats?['total_complaints'] ?? 0}',
                                    Icons.report_problem_outlined,
                                    AppTheme.primary,
                                  ),
                                  _buildStatCard(
                                    'Sudah Diperbaiki',
                                    '${stats?['repaired_complaints'] ?? 0}',
                                    Icons.build_circle_outlined,
                                    AppTheme.statusApproved,
                                  ),
                                  _buildStatCard(
                                    'Diajukan',
                                    '${stats?['pending_complaints'] ?? 0}',
                                    Icons.pending_actions,
                                    AppTheme.statusPending,
                                  ),
                                  _buildStatCard(
                                    'Diterima',
                                    '${stats?['received_complaints'] ?? 0}',
                                    Icons.task_alt,
                                    AppTheme.statusRepairing,
                                  ),
                                ],
                              ),
                            ],
                          ),
                        ),
                      ),
          ),
        ],
      ),
    );
  }
}
