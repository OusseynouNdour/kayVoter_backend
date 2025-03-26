<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    use hasFactory;

    protected $fillable = ['nom', 'prenom', 'email'];

    public function user() {
        return $this->belongsTo(User::class);
    }
}
