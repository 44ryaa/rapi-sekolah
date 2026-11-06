class User {
  final int id;
  final String username;
  final String fullname;
  final String? email;
  final String? phoneNumber;
  final String? role;
  final int? roleId;

  User({
    required this.id,
    required this.username,
    required this.fullname,
    this.email,
    this.phoneNumber,
    this.role,
    this.roleId,
  });

  bool get isAdmin => role == 'Admin' || roleId == 1;
  bool get isWaka => role == 'Waka Sarpras' || roleId == 2;
  bool get isPic => role == 'PIC Ruang' || roleId == 3;
  bool get isGuru => role == 'Guru' || roleId == 4;

  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id: json['id'] as int? ?? 0,
      username: json['username'] as String? ?? '',
      fullname: json['fullname'] as String? ?? '',
      email: json['email'] as String?,
      phoneNumber: json['phone_number'] as String?,
      role: json['role'] as String?,
      roleId: json['role_id'] as int?,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'username': username,
      'fullname': fullname,
      'email': email,
      'phone_number': phoneNumber,
      'role': role,
      'role_id': roleId,
    };
  }
}
