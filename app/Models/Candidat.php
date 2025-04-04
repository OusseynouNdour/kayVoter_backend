<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;

class Candidat extends Model
{
    use HasFactory, HasApiTokens;

    protected $table = 'candidats';

    protected $fillable = ['etudiant_id','ufr_id','election_id','nom', 'prenom', 'photo'];

    public function ufr(){
        return $this->belongsTo(Ufr::class);
    }

    public function liste()
    {
        return $this->HasOne(Liste::class);
    }

    public function election()
    {
        return $this->belongsTo(Election::class);
    }

    public function etudiant()
    {
        return $this->belongsTo(Etudiant::class);
    }
}
