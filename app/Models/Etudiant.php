<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Etudiant extends Model
{
    use HasFactory;

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
}
