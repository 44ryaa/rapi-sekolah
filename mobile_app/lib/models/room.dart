class Room {
  final int id;
  final String roomName;
  final int? capacity;
  final String? location;
  final bool isActive;
  final int? picId;
  final String? picName;

  Room({
    required this.id,
    required this.roomName,
    this.capacity,
    this.location,
    required this.isActive,
    this.picId,
    this.picName,
  });

  factory Room.fromJson(Map<String, dynamic> json) {
    return Room(
      id: json['id'] as int? ?? 0,
      roomName: json['room_name'] as String? ?? '',
      capacity: json['capacity'] as int?,
      location: json['location'] as String?,
      isActive: json['is_active'] == true || json['is_active'] == 1,
      picId: json['pic'] != null ? json['pic']['id'] as int? : null,
      picName: json['pic'] != null ? json['pic']['fullname'] as String? : null,
    );
  }
}
