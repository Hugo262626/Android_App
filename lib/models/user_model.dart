class User {
  final int id;
  final String name;
  final String email;
  final String? description;
  final String? role;

  User({required this.id, required this.name, required this.email, this.description, this.role});

  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id: json['id'],
      name: json['name'],
      email: json['email'],
      description: json['description'],
      role: json['role'],
    );
  }
}
