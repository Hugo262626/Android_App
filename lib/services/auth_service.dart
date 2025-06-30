import 'dart:convert';
import 'dart:io';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:http/http.dart' as http;
import 'package:http_parser/http_parser.dart';
import '../models/user_model.dart';

class AuthService {
  final String baseUrl = 'http://10.0.2.2:8000/api';
  final storage = FlutterSecureStorage();

  Future<Map<String, dynamic>> login(String email, String password) async {
    try {
      print('Tentative de connexion avec: $email');

      final response = await http.post(
        Uri.parse('$baseUrl/login'),
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
        body: jsonEncode({'email': email, 'password': password}),
      );

      print('Status Code: ${response.statusCode}');
      print('Response Body: ${response.body}');

      final data = json.decode(response.body);

      if (response.statusCode == 200 && data['success'] == true) {
        await storage.write(key: 'token', value: data['token']);
        return {
          'success': true,
          'user': data['user'],
          'token': data['token'],
        };
      } else {
        return {
          'success': false,
          'message': data['message'] ?? 'Erreur de connexion',
        };
      }
    } catch (e) {
      print('Erreur lors de la connexion: $e');
      return {
        'success': false,
        'message': 'Erreur de connexion au serveur',
      };
    }
  }

  Future<Map<String, dynamic>> register(Map<String, String> body) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/register'),
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
        body: jsonEncode(body),
      );

      print('Register Status Code: ${response.statusCode}');
      print('Register Response: ${response.body}');

      final data = json.decode(response.body);

      if ((response.statusCode == 200 || response.statusCode == 201) &&
          data['success'] == true) {
        await storage.write(key: 'token', value: data['token']);
        return {
          'success': true,
          'user': data['user'],
          'token': data['token'],
        };
      } else {
        return {
          'success': false,
          'message': data['message'] ?? 'Erreur lors de l\'inscription',
        };
      }
    } catch (e) {
      print('Erreur lors de l\'inscription: $e');
      return {
        'success': false,
        'message': 'Erreur de connexion au serveur',
      };
    }
  }


  Future<List<User>> getUsers() async {
    try {
      final token = await storage.read(key: 'token');
      if (token == null) return [];

      final response = await http.get(
        Uri.parse('$baseUrl/users'),
        headers: {
          'Accept': 'application/json',
          'Authorization': 'Bearer $token',
        },
      );

      if (response.statusCode == 200) {
        final List<dynamic> usersJson = json.decode(response.body);
        return usersJson.map((json) => User.fromJson(json)).toList();
      }

      return [];
    } catch (e) {
      print('Erreur lors de la récupération des utilisateurs: $e');
      return [];
    }
  }

  // NOUVELLES FONCTIONS ADMIN

  // Récupérer tous les utilisateurs (route admin)
  Future<List<User>> getAllUsersAdmin() async {
    try {
      final token = await storage.read(key: 'token');
      if (token == null) throw Exception('Token manquant');

      final response = await http.get(
        Uri.parse('$baseUrl/admin/users'),
        headers: {
          'Accept': 'application/json',
          'Authorization': 'Bearer $token',
        },
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        final List<dynamic> usersJson = data['users'];
        return usersJson.map((json) => User.fromJson(json)).toList();
      } else if (response.statusCode == 403) {
        throw Exception('Accès refusé. Droits admin requis.');
      }

      throw Exception('Erreur lors de la récupération des utilisateurs');
    } catch (e) {
      print('Erreur getAllUsersAdmin: $e');
      throw e;
    }
  }

  // Supprimer un utilisateur (admin uniquement)
  Future<bool> deleteUser(int userId) async {
    try {
      final token = await storage.read(key: 'token');
      if (token == null) return false;

      final response = await http.delete(
        Uri.parse('$baseUrl/admin/users/$userId'),
        headers: {
          'Accept': 'application/json',
          'Authorization': 'Bearer $token',
        },
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        return data['success'] == true;
      } else if (response.statusCode == 403) {
        throw Exception('Accès refusé. Droits admin requis.');
      }

      return false;
    } catch (e) {
      print('Erreur deleteUser: $e');
      throw e;
    }
  }

  // Vérifier si l'utilisateur connecté est admin
  Future<bool> isAdmin() async {
    try {
      final profile = await getProfile();
      if (profile != null && profile['roles'] != null) {
        final roles = List<String>.from(profile['roles']);
        return roles.contains('admin');
      }
      return false;
    } catch (e) {
      print('Erreur isAdmin: $e');
      return false;
    }
  }

  Future<void> logout() async {
    try {
      final token = await storage.read(key: 'token');
      if (token != null) {
        await http.post(
          Uri.parse('$baseUrl/logout'),
          headers: {
            'Accept': 'application/json',
            'Authorization': 'Bearer $token',
          },
        );
      }
    } catch (e) {
      print('Erreur lors de la déconnexion: $e');
    } finally {
      await storage.delete(key: 'token');
    }
  }

  Future<Map<String, dynamic>?> getProfile() async {
    try {
      final token = await storage.read(key: 'token');
      if (token == null) return null;

      final response = await http.get(
        Uri.parse('$baseUrl/profile'),
        headers: {
          'Accept': 'application/json',
          'Authorization': 'Bearer $token',
        },
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        return data['user'];
      }
      return null;
    } catch (e) {
      print('Erreur getProfile: $e');
      return null;
    }
  }

  Future<bool> updateProfileWithPhoto({
    required String name,
    required String email,
    required String birth,
    File? imageFile,
  }) async {
    try {
      final token = await storage.read(key: 'token');
      if (token == null) return false;

      final uri = Uri.parse('$baseUrl/profile');
      final request = http.MultipartRequest('POST', uri);
      request.headers['Authorization'] = 'Bearer $token';
      request.headers['Accept'] = 'application/json';
      request.fields['name'] = name;
      request.fields['email'] = email;
      request.fields['birth'] = birth;

      if (imageFile != null) {
        request.files.add(await http.MultipartFile.fromPath(
          'photo',
          imageFile.path,
          contentType: MediaType('image', 'jpeg'),
        ));
      }

      final streamed = await request.send();
      final response = await http.Response.fromStream(streamed);

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        return data['success'] == true;
      }

      return false;
    } catch (e) {
      print('Erreur updateProfileWithPhoto: $e');
      return false;
    }
  }

  // Vérifier si le token est valide
  Future<bool> isTokenValid() async {
    try {
      final token = await storage.read(key: 'token');
      if (token == null) return false;

      final response = await http.get(
        Uri.parse('$baseUrl/check-token'),
        headers: {
          'Accept': 'application/json',
          'Authorization': 'Bearer $token',
        },
      );

      return response.statusCode == 200;
    } catch (e) {
      print('Erreur isTokenValid: $e');
      return false;
    }
  }
}