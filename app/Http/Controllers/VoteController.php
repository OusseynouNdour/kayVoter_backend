<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class VoteController extends Controller
{
    public function store(Request $request)//gérer le vote
    {
        // Validation des entrées
        $request->validate([
            'liste_id' => 'required|exists:listes,id',
        ]);

        // Récupérer l'étudiant connecté
        $etudiant = Etudiant::where('user_id', Auth::id())->first();
        if (!$etudiant) {
            return response()->json(['message' => 'Étudiant introuvable.'], 404);
        }

        // Vérifier s'il y a une élection en cours pour l'UFR de l'étudiant
        $election = Election::where('ufr_id', $etudiant->ufr_id)
                            ->where('date_debut', '<=', now())
                            ->where('date_fin', '>=', now())
                            ->first();

        if (!$election) {
            return response()->json(['message' => 'Aucune élection en cours pour votre UFR.'], 400);
        }

        // Vérifier si l'étudiant a déjà voté pour cette élection
        $dejaVote = Vote::where('etudiant_id', $etudiant->id)
                        ->where('election_id', $election->id)
                        ->exists();

        if ($dejaVote) {
            return response()->json(['message' => 'Vous avez déjà voté.'], 403);
        }

        // Enregistrer le vote
        Vote::create([
            'etudiant_id' => $etudiant->id,
            'liste_id' => $request->liste_id,
            'election_id' => $election->id
        ]);

        return response()->json(['message' => 'Votre vote a été enregistré avec succès.'], 201);
    }
}
