<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Matiere extends Model
{
    use HasFactory;

    protected $fillable = [
        'nomMatiere',
    ];

    public function classes()
    {
        return $this->belongsToMany(Classe::class, 'classe_matieres');
    }

    public function devoirs()
    {
        return $this->hasMany(Devoir::class);
    }
}
