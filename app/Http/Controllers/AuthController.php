<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    // Suppression de l'appel middleware dans le constructeur
    public function __construct()
    {
        // Ne pas appliquer le middleware ici
    }

    // Inscription
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'birth' => 'required|date',
            'photo' => 'nullable|image|max:2048',
        ]);

        try {
            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('photos', 'public');
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'birth' => $request->birth,
                'photo' => $photoPath,
            ]);

            if (method_exists($user, 'assignRole')) {
                $user->assignRole('user');
            }

            $token = JWTAuth::fromUser($user);
            return response()->json([
                'success' => true,
                'message' => 'Inscription réussie',
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'birth' => $user->birth,
                    'photo' => $user->photo,
                    'roles' => method_exists($user, 'getRoleNames') ? $user->getRoleNames() : [],
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'inscription',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function getAllUsers()
    {
        try {
            $user = JWTAuth::user();
            if (!$user->hasRole('admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès refusé'
                ], 403);
            }

            $users = User::all();
            return response()->json([
                'success' => true,
                'users' => $users
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des utilisateurs',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function deleteUser($id)
    {
        try {
            $user = JWTAuth::user();

            if (!$user->hasRole('admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès refusé'
                ], 403);
            }

            $targetUser = User::find($id);
            if (!$targetUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur non trouvé'
                ], 404);
            }

            $targetUser->delete();

            return response()->json([
                'success' => true,
                'message' => 'Utilisateur supprimé avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression',
                'error' => $e->getMessage()
            ], 500);
        }
    }



    // Connexion
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');
        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Identifiants invalides',
                ], 401);
            }

            $user = JWTAuth::user();
            return response()->json([
                'success' => true,
                'message' => 'Connexion réussie',
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'birth' => $user->birth,
                    'photo' => $user->photo,
                    'roles' => method_exists($user, 'getRoleNames') ? $user->getRoleNames() : [],
                ],
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du token',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Déconnexion
    public function logout(Request $request)
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json([
                'success' => true,
                'message' => 'Déconnexion réussie',
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la déconnexion',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Profil utilisateur
    public function profile()
    {
        try {
            $user = JWTAuth::user();
            return response()->json([
                'success' => true,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'birth' => $user->birth,
                    'photo' => $user->photo,
                    'roles' => method_exists($user, 'getRoleNames') ? $user->getRoleNames() : [],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du profil',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Mise à jour du profil
    public function updateProfile(Request $request)
    {
        try {
            $user = JWTAuth::user();
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|unique:users,email,' . $user->id,
                'birth' => 'required|date',
            ]);

            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'birth' => $request->birth,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Profil mis à jour',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'birth' => $user->birth,
                    'photo' => $user->photo,
                    'roles' => method_exists($user, 'getRoleNames') ? $user->getRoleNames() : [],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du profil',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Méthodes pour les vues web (séparées des API)
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function showRegisterForm()
    {
        return view('auth.register');
    }

    public function getWebProfile()
    {
        $user = Auth::user();
        return view('profile', compact('user'));
    }

    public function updateWebProfile(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users,email,' . $user->id,
            'birth' => 'required|date',
        ]);

        $user->update($request->only('name', 'email', 'birth'));
        return redirect()->route('web.profile')->with('success', 'Profil mis à jour');
    }

    public function getWebUsers()
    {
        $users = User::all();
        return view('app', compact('users'));
    }
}
