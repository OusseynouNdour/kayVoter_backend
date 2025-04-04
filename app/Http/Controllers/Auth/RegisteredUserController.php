<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Etudiant;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): Response|JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'nom' => 'required|string|max:255',
                'prenom' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'ufr' => 'required|integer|exists:ufrs,id',  // Vérifie que l'UFR existe
                'matricule' => 'required|string|max:20',
                'password' => 'required|confirmed|min:8',
            ]);

            // Créer l'utilisateur
            $user = User::create([
                'nom' => $validatedData['nom'],
                'prenom' => $validatedData['prenom'],
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
            ]);

            // Créer l'étudiant
            $etudiant = Etudiant::create([
                'user_id' => $user->id,
                'ufr_id' => $validatedData['ufr'],
                'matricule' => $validatedData['matricule'],
            ]);

            return response()->json(['message' => 'Inscription réussie'], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de l’inscription',
                'details' => $e->getMessage(),
            ], 500);
        }
    }
}
