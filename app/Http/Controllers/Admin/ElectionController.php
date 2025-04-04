<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Election;
use App\Models\Ufr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ElectionController extends Controller
{
    public function index()
    {
        $elections = Election::with('ufr')->latest()->get();
        return response()->json($elections);
    }

    public function activeElections()
    {
        $elections = Election::with('ufr')
            ->where('date_fin', '>', now())
            ->get();

        return response()->json($elections);
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ufr_id' => 'required|exists:ufrs,id',
            'date_debut' => 'required|date|after:now',
            'date_fin' => 'required|date|after:date_debut'
        ]);

        if ($validator->fails()) {
            \Log::debug('Échec validation:', $validator->errors()->toArray());
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Vérifier qu'il n'y a pas de chevauchement
        $existing = Election::where('ufr_id', $request->ufr_id)
            ->where(function($query) use ($request) {
                $query->whereBetween('date_debut', [$request->date_debut, $request->date_fin])
                      ->orWhereBetween('date_fin', [$request->date_debut, $request->date_fin]);
            })->exists();

        if ($existing) {
            return response()->json(['message' => 'Une élection existe déjà pour cette période'], 409);
        }

        $election = Election::create($request->all());

        return response()->json($election->load('ufr'), 201);
    }

    public function destroy(Election $election)
    {
        // Empêcher la suppression si l'élection a commencé
        if ($election->date_debut < now()) {
            return response()->json(['message' => 'Impossible de supprimer une élection en cours ou terminée'], 403);
        }

        $election->delete();
        return response()->json(null, 204);
    }
}
