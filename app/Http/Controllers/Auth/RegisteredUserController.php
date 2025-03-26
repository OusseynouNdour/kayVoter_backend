<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Etudiant;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Log;

class RegisteredUserController extends Controller
{
    public function store(Request $request)
    {
        // Validation des données
        $validatedData = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'prenom' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users'],
            'ufr' => ['required', 'integer'],
            'matricule' => ['required', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        try {
            // Création de l'utilisateur
            $user = User::create([
                'nom' => $validatedData['nom'],
                'prenom' => $validatedData['prenom'],
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
            ]);

            // Création de l'étudiant avec le user_id
            $etudiant = Etudiant::create([
                'user_id' => $user->id,
                'ufr_id' => $validatedData['ufr'],
                'matricule' => $validatedData['matricule'],
            ]);

            // Log de succès
            Log::info('✅ Inscription réussie', ['user' => $user, 'etudiant' => $etudiant]);

            // Déclenchement de l'événement de création d'utilisateur
            event(new Registered($user));

            // Authentification automatique
            Auth::login($user);

            // Réponse JSON
            return response()->json([
                'message' => 'Inscription réussie',
                'user' => $user,
                'etudiant' => $etudiant,
            ], 201);
        } catch (\Exception $e) {
            Log::error('❌ Erreur lors de l’inscription : ' . $e->getMessage());
            return response()->json([
                'error' => 'Erreur lors de l’inscription',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
