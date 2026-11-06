class ComplaintModel {
  final int id;
  final String description;
  final String status;
  final String? statusRepair;
  final String? repairNote;
  final String? rejectReason;
  final String? photoUrl;
  final int? roomId;
  final String? roomName;
  final String? reporterName;
  final String? handlerName;
  final String? reportedAt;
  final String? updatedAt;

  ComplaintModel({
    required this.id,
    required this.description,
    required this.status,
    this.statusRepair,
    this.repairNote,
    this.rejectReason,
    this.photoUrl,
    this.roomId,
    this.roomName,
    this.reporterName,
    this.handlerName,
    this.reportedAt,
    this.updatedAt,
  });

  factory ComplaintModel.fromJson(Map<String, dynamic> json) {
    return ComplaintModel(
      id: json['id'] as int? ?? 0,
      description: json['description'] as String? ?? '',
      status: json['status'] as String? ?? 'Diajukan',
      statusRepair: json['status_repair'] as String?,
      repairNote: json['repair_note'] as String?,
      rejectReason: json['reject_reason'] as String?,
      photoUrl: json['photo_url'] as String?,
      roomId: json['room'] != null ? json['room']['id'] as int? : null,
      roomName: json['room'] != null ? json['room']['room_name'] as String? : null,
      reporterName: json['reporter'] != null ? json['reporter']['fullname'] as String? : null,
      handlerName: json['handler'] != null ? json['handler']['fullname'] as String? : null,
      reportedAt: json['reported_at'] as String?,
      updatedAt: json['updated_at'] as String?,
    );
  }
}
