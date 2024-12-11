<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Soumission extends Model
{
    use HasFactory;

    // Définir la table associée
    protected $table = 'soumissions';

    // Colonnes modifiables
    protected $fillable = [
        'dateAttribution',
        'dateSoumission',
        'soumis',
        'note',
        'commentaire',
        'devoir_id',
        'eleve_id',
    ];

    // Relation avec le modèle Devoir
    public function devoir()
    {
        return $this->belongsTo(Devoir::class);
    }

    // Relation avec le modèle Eleve
    public function eleve()
    {
        return $this->belongsTo(Eleve::class);
    }

    // Accesseurs ou mutateurs pour le format des dates et le booléen
    protected $casts = [
        'dateAttribution' => 'date',
        'dateSoumission' => 'date',
        'soumis' => 'boolean',
    ];

    // Scopes personnalisés

    /**
     * Scope pour récupérer les devoirs non soumis.
     */
    public function scopeNonSoumis($query)
    {
        return $query->where('soumis', false);
    }

    /**
     * Scope pour filtrer par élève.
     */
    public function scopeByEleve($query, $eleveId)
    {
        return $query->where('eleve_id', $eleveId);
    }

    /**
     * Scope pour filtrer par devoir.
     */
    public function scopeByDevoir($query, $devoirId)
    {
        return $query->where('devoir_id', $devoirId);
    }

    // Méthodes supplémentaires

    /**
     * Vérifier si une soumission est en retard.
     */
    public function estEnRetard()
    {
        return $this->dateSoumission < now() && !$this->soumis;
    }

    /**
     * Calculer la note finale (exemple fictif, ajustez selon vos besoins).
     */
    public function calculerNoteFinale()
    {
        return $this->note ? round($this->note, 2) : null;
    }
}
