<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Programme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProgrammeController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'programme_pdf' => 'required|mimes:pdf|max:2048' // 2MB max
        ]);

        $path = $request->file('programme_pdf')->store('programmes', 'public');

        $programme = Programme::create([
            'file_path' => $path
        ]);


        return response()->json([
            'message' => 'Programme uploadé avec succès',
            'programme_id' => $programme->id,
            'file_url' => $programme->file_url
        ], 201);
    }

    public function update(Request $request, Programme $programme)
    {
        $request->validate([
            'programme_pdf' => 'required|mimes:pdf|max:2048'
        ]);

        // Supprime l'ancien fichier
        Storage::disk('public')->delete($programme->file_path);

        $path = $request->file('programme_pdf')->store('programmes', 'public');

        $programme->update(['file_path' => $path]);

        return response()->json([
            'message' => 'Programme mis à jour avec succès',
            'file_url' => $programme->fresh()->file_url
        ]);
    }
}
