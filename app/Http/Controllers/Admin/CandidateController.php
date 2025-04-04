<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Candidat;
use App\Models\Election;
use App\Models\Etudiant;
use App\Models\Liste;
use App\Models\Programme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CandidateController extends Controller
{
    public function index()
    {
        \Log::info('Requête candidates avec relations:', request()->all());

        $candidates = Candidat::with(['election.ufr', 'liste', 'etudiant.user'])
            ->get()
            ->map(function ($candidate) {
                return [
                    'id' => $candidate->id,
                    'nom' => $candidate->nom,
                    'prenom' => $candidate->prenom,
                    'photo' => $candidate->photo,
                    'election' => [
                        'id' => $candidate->election->id,
                        'ufr' => [
                            'nom' => $candidate->election->ufr->nom
                        ]
                    ],
                    'liste' => $candidate->liste ? [
                        'nom' => $candidate->liste->nom
                    ] : null
                ];
            });

        \Log::info('Candidats retournés:', $candidates->toArray());

        return response()->json($candidates);
    }

    public function store(Request $request)
    {
        $request->validate([
            'election_id' => 'required|exists:elections,id',
            'etudiant_id' => 'required|exists:etudiants,id',
            'liste_nom' => 'required|string|max:255',
            'photo' => 'nullable|image|max:2048',
            'programme_pdf' => 'required|file|mimes:pdf|max:5120'
        ]);

        // Vérifier que l'étudiant n'est pas déjà candidat
        $existingCandidate = Candidate::where('etudiant_id', $request->etudiant_id)
            ->where('election_id', $request->election_id)
            ->exists();

        if ($existingCandidate) {
            return response()->json(['message' => 'Cet étudiant est déjà candidat pour cette élection'], 409);
        }

        // Upload du programme PDF
        $programmePath = $request->file('programme_pdf')->store('programmes', 'public');
        $programme = Programme::create(['file_path' => $programmePath]);

        // Upload de la photo si fournie
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('candidates/photos', 'public');
        }

        // Création de la liste
        $liste = Liste::create([
            'nom' => $request->liste_nom,
            'programme_id' => $programme->id
        ]);

        // Création du candidat
        $candidate = Candidate::create([
            'etudiant_id' => $request->etudiant_id,
            'election_id' => $request->election_id,
            'liste_id' => $liste->id,
            'photo' => $photoPath
        ]);

        return response()->json($candidate->load(['etudiant.user', 'election.ufr', 'liste.programme']), 201);
    }

    public function destroy(Candidate $candidate)
    {
        // Supprimer les fichiers associés
        if ($candidate->photo) {
            Storage::disk('public')->delete($candidate->photo);
        }

        if ($candidate->liste && $candidate->liste->programme) {
            Storage::disk('public')->delete($candidate->liste->programme->file_path);
            $candidate->liste->programme->delete();
        }

        $candidate->liste()->delete();
        $candidate->delete();

        return response()->json(null, 204);
    }
}
