<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ufr;
use App\Models\Election;
use Illuminate\Support\Facades\DB;

class ElectionResultsController extends Controller
{
    public function getResults($ufr_id)
{
    try {
        // 1. Vérification UFR
        $ufr = Ufr::find($ufr_id);
        if (!$ufr) {
            return response()->json(['error' => 'UFR non trouvée'], 404);
        }

        // 2. Récupérer l'élection en cours pour cette UFR
        $election = Election::where('ufr_id', $ufr_id)
                    ->where('date_debut', '<=', now())
                    ->where('date_fin', '>=', now())
                    ->first();

        if (!$election) {
            return response()->json(['error' => 'Aucune élection en cours'], 404);
        }

        // 3. Compter les votes par liste (DÉFINIR $results ICI)
        $results = DB::table('votes')
                   ->join('listes', 'votes.liste_id', '=', 'listes.id')
                   ->where('votes.election_id', $election->id)
                   ->select('listes.id', 'listes.nom as liste_name', DB::raw('COUNT(*) as votes_count'))
                   ->groupBy('listes.id', 'listes.nom')
                   ->get();

        // 4. Calculer le total des votes
        $totalVotes = $results->sum('votes_count');

        // 5. Formater les résultats
        $formattedResults = $results->map(function ($item) use ($totalVotes) {
            return [
                'liste_id' => $item->id,
                'liste_name' => $item->liste_name,
                'votes' => $item->votes_count,
                'percentage' => $totalVotes > 0 ? round(($item->votes_count / $totalVotes) * 100, 2) : 0,
                'color' => $this->getColorForList($item->id)
            ];
        });

        return response()->json([
            'ufr_name' => $ufr->nom,
            'election_id' => $election->id,
            'total_votes' => $totalVotes,
            'results' => $formattedResults
        ]);

    } catch (\Exception $e) {
        \Log::error("Erreur dans getResults: " . $e->getMessage());
        return response()->json(['error' => 'Erreur serveur'], 500);
    }
}

    private function getColorForList($liste_id)
    {
        $colors = ['#3498db', '#e74c3c', '#2ecc71', '#f1c40f', '#9b59b6', '#1abc9c'];
        return $colors[$liste_id % count($colors)];
    }
}
