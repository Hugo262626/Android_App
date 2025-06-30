import 'package:flutter/material.dart';
import '../models/user_model.dart';
import '../services/auth_service.dart';
import 'login_screen.dart';
import 'edit_profile_page.dart';

class UserListScreen extends StatefulWidget {
  const UserListScreen({super.key});

  @override
  State<UserListScreen> createState() => _UserListScreenState();
}

class _UserListScreenState extends State<UserListScreen> {
  final authService = AuthService();
  List<User> users = [];
  bool isAdmin = false;
  bool isLoading = true;

  @override
  void initState() {
    super.initState();
    _initializeScreen();
  }

  Future<void> _initializeScreen() async {
    await _checkAdminStatus();
    await loadUsers();
  }

  Future<void> _checkAdminStatus() async {
    try {
      final adminStatus = await authService.isAdmin();
      setState(() {
        isAdmin = adminStatus;
      });
    } catch (e) {
      print('Erreur lors de la vérification des droits admin: $e');
    }
  }

  Future<void> loadUsers() async {
    setState(() {
      isLoading = true;
    });

    try {
      List<User> data;
      if (isAdmin) {
        // Si admin, utiliser la route admin pour voir tous les utilisateurs
        data = await authService.getAllUsersAdmin();
      } else {
        // Sinon, utiliser la route normale
        data = await authService.getUsers();
      }

      setState(() {
        users = data;
        isLoading = false;
      });
    } catch (e) {
      setState(() {
        isLoading = false;
      });

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Erreur lors du chargement: $e'),
            backgroundColor: Colors.red,
          ),
        );
      }
    }
  }

  Future<void> _deleteUser(User user) async {
    // Confirmation avant suppression
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (BuildContext context) {
        return AlertDialog(
          title: const Text('Confirmer la suppression'),
          content: Text('Êtes-vous sûr de vouloir supprimer l\'utilisateur "${user.name}" ?'),
          actions: [
            TextButton(
              onPressed: () => Navigator.of(context).pop(false),
              child: const Text('Annuler'),
            ),
            TextButton(
              onPressed: () => Navigator.of(context).pop(true),
              style: TextButton.styleFrom(foregroundColor: Colors.red),
              child: const Text('Supprimer'),
            ),
          ],
        );
      },
    );

    if (confirmed != true) return;

    try {
      final success = await authService.deleteUser(user.id);
      if (success) {
        setState(() {
          users.removeWhere((u) => u.id == user.id);
        });

        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('Utilisateur supprimé avec succès'),
              backgroundColor: Colors.green,
            ),
          );
        }
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Erreur lors de la suppression: $e'),
            backgroundColor: Colors.red,
          ),
        );
      }
    }
  }

  void logout() async {
    await authService.logout();
    if (mounted) {
      Navigator.pushReplacement(
        context,
        MaterialPageRoute(builder: (_) => const LoginScreen()),
      );
    }
  }

  void goToEditProfile() {
    Navigator.push(
      context,
      MaterialPageRoute(builder: (_) => const EditProfilePage()),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(isAdmin ? 'Utilisateurs (Admin)' : 'Utilisateurs'),
        backgroundColor: isAdmin ? Colors.orange : null,
        actions: [
          IconButton(
            onPressed: loadUsers,
            icon: const Icon(Icons.refresh),
            tooltip: 'Actualiser',
          ),
          IconButton(
            onPressed: logout,
            icon: const Icon(Icons.logout),
          ),
          IconButton(
            onPressed: goToEditProfile,
            icon: const Icon(Icons.edit),
            tooltip: 'Modifier mon profil',
          ),
        ],
      ),
      body: isLoading
          ? const Center(child: CircularProgressIndicator())
          : users.isEmpty
          ? const Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.people_outline, size: 64, color: Colors.grey),
            SizedBox(height: 16),
            Text('Aucun utilisateur trouvé'),
          ],
        ),
      )
          : RefreshIndicator(
        onRefresh: loadUsers,
        child: ListView.builder(
          itemCount: users.length,
          itemBuilder: (context, index) {
            final u = users[index];
            final isUserAdmin = u.role == 'admin';

            return Card(
              margin: const EdgeInsets.all(8),
              child: ListTile(
                leading: CircleAvatar(
                  backgroundColor: isUserAdmin ? Colors.orange : Colors.blue,
                  child: Icon(
                    isUserAdmin ? Icons.admin_panel_settings : Icons.person,
                    color: Colors.white,
                  ),
                ),
                title: Text(
                  u.name,
                  style: const TextStyle(fontWeight: FontWeight.bold),
                ),
                subtitle: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    if (u.description != null && u.description!.isNotEmpty)
                      Text(u.description!),
                    if (isUserAdmin)
                      Container(
                        margin: const EdgeInsets.only(top: 4),
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                        decoration: BoxDecoration(
                          color: Colors.orange,
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: const Text(
                          'ADMIN',
                          style: TextStyle(
                            color: Colors.white,
                            fontSize: 10,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ),
                  ],
                ),
                trailing: isAdmin
                    ? PopupMenuButton<String>(
                  onSelected: (String choice) {
                    if (choice == 'delete') {
                      _deleteUser(u);
                    }
                  },
                  itemBuilder: (BuildContext context) {
                    return [
                      const PopupMenuItem<String>(
                        value: 'delete',
                        child: Row(
                          children: [
                            Icon(Icons.delete, color: Colors.red),
                            SizedBox(width: 8),
                            Text('Supprimer'),
                          ],
                        ),
                      ),
                    ];
                  },
                )
                    : Text(
                  u.role ?? '',
                  style: const TextStyle(color: Colors.purple),
                ),
              ),
            );
          },
        ),
      ),
    );
  }
}