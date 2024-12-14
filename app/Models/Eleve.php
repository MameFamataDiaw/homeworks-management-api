<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Eleve extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'parentt_id',
        'classe_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function classe()
    {
        return $this->belongsTo(Classe::class, 'classe_id'); // Vérifiez que 'classe_id' correspond bien à votre base de données.
    }

    public function parent()
    {
        return $this->belongsTo(Parentt::class, 'parentt_id');
    }

    public function devoirsAssignes()
    {
        return $this->belongsToMany(Devoir::class, 'soumissions')
                    ->withPivot('dateAttribution', 'aRendre', 'soumis', 'dateSoumission', 'document', 'note', 'commentaire')
                    ->withTimestamps();
    }

}
