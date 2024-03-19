<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class ApiController extends Controller
{
    //S'inscrire (POST, formulaire)

    public function register(Request $request)
    {
        //validation des données
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed',
        ]);

        //Insértion dans la base de données

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        //Répense

        return response()->json([
            'status' => true,
            'message' => 'Inscription reussie',
        ]);
    }

    //S'authentifier (POST, formulaire)

    public function login(Request $request)
    {

        //validation des données
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Géneration du clé JWT

        $token = JWTAuth::attempt([
            'email' => $request->email,
            'password' => $request->password,
        ]);

        if (! empty($token)) {

            return response()->json([
                'status' => true,
                'message' => 'Authentification reussie',
                'token' => $token,
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'Authentification échouée',
        ]);
    }

    // Voir le profile (GET)

    public function profile()
    {
        $userdata = auth()->user();

        return response()->json([
            'status' => true,
            'message' => 'Profile data',
            'data' => $userdata
        ]);
    }


    //  Regénérer le Token (GET)

    public function refreshToken(){
        $newToken= auth()->refresh();

        return response()->json([
            "status" => true,
            "message" => "Nouveau Token Générer",
            "token" => $newToken
        ]); 
    }


    // Se déconnecter (GET)

    public function logout(){

        auth()->logout();
        
        return response()->json([
            "status" => true,
            "message" => "L'utilisateur déconnecter avec succées"
        ]);
     } 
}
