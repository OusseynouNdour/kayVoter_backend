<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Etudiant;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index()
    {
        $students = Etudiant::with(['user', 'ufr'])
            ->whereDoesntHave('candidat')
            ->get();

        return response()->json($students);
    }
}
