<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;

class Etudiant extends Model
{
    use HasFactory, HasApiTokens;

    protected $fillable = ['user_id', 'ufr_id', 'matricule'];

    public function ufr() {
        return $this->belongsTo(Ufr::class);
    }

    public function vote() {
        return $this->hasOne(Vote::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function candidat()
    {
        return $this->hasOne(Candidat::class, 'etudiant_id');
    }
}
