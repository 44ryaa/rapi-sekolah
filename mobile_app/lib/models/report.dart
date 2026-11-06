class ReportSummary {
  final int totalEvents;
  final int approvedEvents;
  final int pendingEvents;
  final int rejectedEvents;
  final int totalComplaints;
  final int receivedComplaints;
  final int pendingComplaints;
  final int repairedComplaints;

  ReportSummary({
    required this.totalEvents,
    required this.approvedEvents,
    required this.pendingEvents,
    required this.rejectedEvents,
    required this.totalComplaints,
    required this.receivedComplaints,
    required this.pendingComplaints,
    required this.repairedComplaints,
  });

  factory ReportSummary.fromJson(Map<String, dynamic> json) {
    return ReportSummary(
      totalEvents: json['total_events'] as int? ?? 0,
      approvedEvents: json['approved_events'] as int? ?? 0,
      pendingEvents: json['pending_events'] as int? ?? 0,
      rejectedEvents: json['rejected_events'] as int? ?? 0,
      totalComplaints: json['total_complaints'] as int? ?? 0,
      receivedComplaints: json['received_complaints'] as int? ?? 0,
      pendingComplaints: json['pending_complaints'] as int? ?? 0,
      repairedComplaints: json['repaired_complaints'] as int? ?? 0,
    );
  }
}
