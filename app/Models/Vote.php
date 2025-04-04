<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;

class Vote extends Model
{
    use HasFactory, HasApiTokens;

    protected $fillable = ['etudiant_id', 'liste_id', 'election_id'];

    public function etudiant() {
        return $this->belongsTo(Etudiant::class);
    }

    public function election() {
        return $this->belongsTo(Election::class);
    }

    public function liste() {
        return $this->belongsTo(Liste::class);
    }
}
