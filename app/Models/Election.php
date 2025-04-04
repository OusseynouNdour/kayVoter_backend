<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;

class Election extends Model
{
    use HasFactory, HasApiTokens;

    protected $fillable = ['ufr_id', 'date_debut', 'date_fin'];

    public function candidats()
    {
        return $this->hasMany(Candidat::class);
    }

    public function votes()
    {
        return $this->hasMany(Vote::class);
    }

    public function ufr()
    {
        return $this->belongsTo(Ufr::class);
    }

    public function getResultatsAttribute()
    {
        return $this->votes()
            ->select('liste_id', DB::raw('count(*) as votes'))
            ->groupBy('liste_id')
            ->orderByDesc('votes')
            ->get()
            ->map(function($item) {
                $item->liste->load('candidat');
                return $item;
            });
    }
}
