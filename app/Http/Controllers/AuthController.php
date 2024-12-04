<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    private $secretKey = "vMM4OVhAOkDSw/8GGo3TYIvk8H52HF0jZgkVcrwEfbU=
        vMM4OVhAOkDSw/8GGo3TYIvk8H52HF0jZgkVcrwEfbU=";

    /**
     * this method register a new user.
     */
    public function register (Request $request) {
        $fields = $request->all();
        $fields['role'] = $fields['role'] ?? 'admin';

        $errors = Validator::make($fields, [
            'prenom' => 'required|string|max:50',
            'nom' => 'required|string|max:50',
            'genre' => 'required|in:masculin,feminin',
            'role' => 'in:admin,enseignant,parent,eleve',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($errors->fails()) {
            return response($errors->errors()->all(),422);
        }

        $user = User::create([
            'prenom' => $fields['prenom'],
            'nom' => $fields['nom'],
            'genre' => $fields['genre'],
            'role' => $fields['role'],
            'email' => $fields['email'],
            'password' => bcrypt($fields['password']),
        ]);

        return response()->json([
            'message' => 'Votre compte a été crée !',
            'user' => $user,
        ]);

    }

    /**
     * this method login a user
     */
    public function login (Request $request)
    {
        $fields = $request->all();

        $errors = Validator::make($fields,[
            'email' => 'required|string|email',
            'password' => 'required',
        ]);

        if ($errors->fails()) {
            return response($errors->errors()->all(),422);
        }

        $user = User::where('email',$fields['email'])->first();

        if (!$user || !Hash::check($fields['password'],$user->password)) {

            return response()->json([
                'status' => false,
                'message' => 'email ou mot de passe invalide.',
            ], 401);
        }

        $token = $user->createToken($this->secretKey)->plainTextToken;

        // Vérifier si l'utilisateur n'est pas un administrateur
        if ($user->role != 'admin' && $user->first_login) {
            return response()->json([
                'status' => true,
                'action_required' => 'change_password',
                'message' => 'Merci de changer votre mot de passe lors de votre première connexion.',
                'user' => $user,
                'token' => $token, // Inclure le token ici aussi
            ], 200);
        }

            return response()->json([
                'user' => $user,
                'token' => $token
            ]);
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'status' => true,
                'message' => 'Logged out successfully'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function userIdLoggedIn(Request $request){
        return response(['message'=>''],200);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'password' => 'required|min:8|confirmed',
        ]);

        $user = Auth::user();
        $user->password = bcrypt($request->password);
        $user->first_login = false; // Marquer comme ayant changé le mot de passe
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Mot de passe modifié avec succès.',
            'user' => [
                'id' => $user->id,
                'prenom' => $user->prenom,
                'nom' => $user->nom,
                'role' => $user->role, // Ajout du rôle
            ],
        ]);
    }

}
