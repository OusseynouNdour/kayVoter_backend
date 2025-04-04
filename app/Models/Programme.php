<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;

class Programme extends Model
{
    use HasApiTokens, HasFactory;
    protected $fillable = ['file_path'];

    public function liste()
    {
        return $this->hasOne(Liste::class);
    }

    public function getFileUrlAttribute()
    {
        return $this->file_path ? asset('storage/' . $this->file_path) : null;
    }


}
