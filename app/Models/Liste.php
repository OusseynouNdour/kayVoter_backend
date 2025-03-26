<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Liste extends Model
{
    use HasFactory;

    protected $fillable = ['nom', 'candidat_id'];

    public function candidat() {
        return $this->hasOne(Candidat::class);
    }

    public function votes() {
        return $this->hasMany(Vote::class);
    }

    public function programme() {
        return $this->hasOne(Programme::class);
    }
}
