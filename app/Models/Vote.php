<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
    use HasFactory;

    protected $fillable = ['etudiant_id', 'liste_id'];

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
