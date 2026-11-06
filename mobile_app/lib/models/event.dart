class EventModel {
  final int id;
  final String eventName;
  final String date;
  final String startTime;
  final String endTime;
  final String status;
  final String? rejectReason;
  final int? roomId;
  final String? roomName;
  final String? location;
  final String? requesterName;
  final String? approverName;
  final String? createdAt;

  EventModel({
    required this.id,
    required this.eventName,
    required this.date,
    required this.startTime,
    required this.endTime,
    required this.status,
    this.rejectReason,
    this.roomId,
    this.roomName,
    this.location,
    this.requesterName,
    this.approverName,
    this.createdAt,
  });

  factory EventModel.fromJson(Map<String, dynamic> json) {
    return EventModel(
      id: json['id'] as int? ?? 0,
      eventName: json['event_name'] as String? ?? '',
      date: json['date'] as String? ?? '',
      startTime: json['start_time'] as String? ?? '',
      endTime: json['end_time'] as String? ?? '',
      status: json['status'] as String? ?? 'Diajukan',
      rejectReason: json['reject_reason'] as String?,
      roomId: json['room'] != null ? json['room']['id'] as int? : null,
      roomName: json['room'] != null ? json['room']['room_name'] as String? : null,
      location: json['room'] != null ? json['room']['location'] as String? : null,
      requesterName: json['requester'] != null ? json['requester']['fullname'] as String? : null,
      approverName: json['approver'] != null ? json['approver']['fullname'] as String? : null,
      createdAt: json['created_at'] as String?,
    );
  }
}
