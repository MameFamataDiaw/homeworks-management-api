<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classe extends Model
{
    use HasFactory;

    protected $fillable = [
        'nomClasse',
        'niveau',
        'effectif'
    ];

    public function matieres()
    {
        return $this->belongsToMany(Matiere::class, 'classe_matieres');
    }

    public function eleves()
    {
        return $this->hasMany(Eleve::class, 'classe_id');
    }

    public function enseignant()
    {
        return $this->belongsTo(User::class, 'enseignant_id');
    }

    public function devoirs()
    {
        return $this->hasMany(Devoir::class, 'classe_id');
    }
}

