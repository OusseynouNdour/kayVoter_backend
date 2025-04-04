<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;

class Liste extends Model
{
    use HasFactory, HasApiTokens;

    protected $fillable = ['nom', 'candidat_id', 'programme_id'];

    public function candidat() {
        return $this->belognsTo(Candidat::class);
    }

    public function votes() {
        return $this->hasMany(Vote::class);
    }

    public function programme() // Singulier !
    {
        return $this->belongsTo(Programme::class, 'programme_id');
    }
}
