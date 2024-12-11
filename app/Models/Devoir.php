<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Devoir extends Model
{
    use HasFactory;

    protected $fillable = [
        'module',
        'contenu',
        'dateAjout',
        'document',
        'classe_id',
        'matiere_id',
    ];

    public function classe()
    {
        return $this->belongsTo(Classe::class, 'classe_id');
    }

    public function matiere()
    {
        return $this->belongsTo(Matiere::class);
    }

    public function eleves()
    {
        return $this->belongsToMany(Eleve::class, 'soumissions')->withTimestamps();
    }
}
