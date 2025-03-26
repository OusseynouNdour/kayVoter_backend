<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Candidat extends Model
{
    use HasFactory;

    protected $fillable = ['nom', 'prenom'];

    public function ufr(){
        return $this->belongsTo(Ufr::class);
    }

    public function liste()
    {
        return $this->belongsTo(Liste::class);
    }

    public function election()
    {
        return $this->belongsTo(Election::class);
    }
}
