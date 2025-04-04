<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;

class Ufr extends Model
{
    use HasFactory, HasApiTokens;

    protected $fillable =  ['nom'];
}
