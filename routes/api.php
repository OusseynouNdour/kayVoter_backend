<?php

use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\VoteController;
use App\Models\{Election, Etudiant, Vote};
use App\Http\Controllers\Admin\{
    AdminController,
    CandidateController,
    ElectionController,
    ProgrammeController,
    StudentController,

};
use App\Http\Controllers\ElectionResultsController;
use App\Models\Ufr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});
Route::get('/ufrs', function () {
    return Ufr::all();
});

Route::post('/register', [RegisteredUserController::class, 'store']);
Route::post('/vote', [VoteController::class, 'store']);

Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function() {
    // Statistiques
    Route::get('/stats', [AdminController::class, 'stats']);

    // Gestion des élections
    Route::get('/elections', [ElectionController::class, 'index']);
    Route::get('/active-elections', [ElectionController::class, 'activeElections']);
    Route::post('/elections', [ElectionController::class, 'store']);
    Route::delete('/elections/{election}', [ElectionController::class, 'destroy']);

    // Gestion des candidats
    Route::get('/candidates', [CandidateController::class, 'index']);
    Route::post('/candidates', [CandidateController::class, 'store']);
    Route::delete('/candidates/{candidate}', [CandidateController::class, 'destroy']);

    // Liste des étudiants non candidats
    Route::get('/students', [StudentController::class, 'index']);
});

// routes/api.php
Route::middleware('auth:sanctum')->get('/student/current-election', function (Request $request) {
    $user = $request->user();
    $etudiant = Etudiant::where('user_id', $user->id)->first();

    if (!$etudiant) {
        return response()->json(['message' => 'Profil étudiant introuvable'], 404);
    }

    // Charge l'élection avec toutes les relations nécessaires
    $election = Election::with(['candidats' => function($query) {
        $query->with(['liste' => function($q) {
            $q->with('programme');
        }]);
    }])
    ->where('ufr_id', $etudiant->ufr_id)
    ->where('date_debut', '<=', now())
    ->where('date_fin', '>=', now())
    ->first();

    if (!$election) {
        return response()->json(['message' => 'Aucune élection en cours pour votre UFR'], 404);
    }

    // Formate les candidats avec vérification des relations
    $formattedCandidates = $election->candidats->map(function($candidate) {
        $programmeData = null;

        // Vérification en cascade des relations
        if ($candidate->liste && $candidate->liste->programme) {
            $programmeData = [
                'file_path' => asset($candidate->liste->programme->file_path),
                'id_liste' => $candidate->liste->id
            ];
        }

        return [
            'id' => $candidate->id,
            'nom' => $candidate->nom,
            'prenom' => $candidate->prenom,
            'photo' => $candidate->photo ? asset($candidate->photo) : null,
            'ufr' => $candidate->ufr->nom ?? 'UFR non spécifiée',
            'programme' => $programmeData
        ];
    });

    return response()->json([
        'electionId' => $election->id,
        'candidates' => $formattedCandidates
    ]);
});

Route::middleware('auth:sanctum')->get('/elections/{election}/has-voted', function (Request $request, Election $election) {
    $user = $request->user();

    $etudiant = Etudiant::where('user_id', $user->id)->first();

    if (!$etudiant) {
        return response()->json([
            'error' => 'Profil étudiant introuvable',
            'details' => "L'utilisateur #$user->id n'a pas de profil étudiant associé"
        ], 404);
    }

    $hasVoted = Vote::where('election_id', $election->id)
                  ->where('etudiant_id', $etudiant->id)
                  ->exists();

    return response()->json($hasVoted);
});


Route::middleware('auth:sanctum')->post('/vote', function (Request $request) {
    // Activer le logging pour débogage
    \Log::info('Tentative de vote reçue', $request->all());

    $validated = $request->validate([
        'election_id' => 'required|exists:elections,id',
        'liste_id' => 'required|exists:listes,id'
    ]);

    $user = $request->user();
    \Log::info('Utilisateur authentifié', [$user]);

    $etudiant = Etudiant::where('user_id', $user->id)->firstOrFail();
    \Log::info('Profil étudiant trouvé', [$etudiant]);

    // Vérifie si l'étudiant a déjà voté
    if (Vote::where('election_id', $validated['election_id'])
           ->where('etudiant_id', $etudiant->id)
           ->exists()) {
        return response()->json(['message' => 'Vous avez déjà voté'], 403);
    }

    // Enregistre le vote
    try {
        $vote = Vote::create([
            'election_id' => $validated['election_id'],
            'liste_id' => $validated['liste_id'],
            'etudiant_id' => $etudiant->id,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        \Log::info('Vote créé avec succès', [$vote]);

        return response()->json(['message' => 'Vote enregistré avec succès']);
    } catch (\Exception $e) {
        \Log::error('Erreur création vote', ['error' => $e->getMessage()]);
        return response()->json(['message' => 'Erreur serveur'], 500);
    }

});


Route::get('/results/{ufr_id}', [ElectionResultsController::class, 'getResults']);

