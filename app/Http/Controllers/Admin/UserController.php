<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // Liste des utilisateurs
    public function index()
    {
        $users = User::all();
        return response()->json([
            'success' => true,
            'users' => $users
        ]);
    }

    // Supprimer un utilisateur
    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);

            // Empêcher la suppression de soi-même
            if (auth()->id() === $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous ne pouvez pas vous supprimer vous-même.'
                ], 403);
            }

            // Empêcher la suppression d'autres admins (optionnel)
            if ($user->role === 'admin' && auth()->user()->role === 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous ne pouvez pas supprimer un autre administrateur.'
                ], 403);
            }

            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'Utilisateur supprimé avec succès.'
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non trouvé.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de l\'utilisateur.'
            ], 500);
        }
    }
}
