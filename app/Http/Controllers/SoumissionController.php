<?php

namespace App\Http\Controllers;

use App\Models\Eleve;
use App\Models\User;
use App\Models\Soumission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Devoir;


class SoumissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function getSoumissions($devoirId)
    {
        $user = Auth::user();

        // Vérifiez que l'utilisateur est un enseignant
        if ($user->role !== 'enseignant') {
            return response()->json([
                'status' => false,
                'message' => 'Accès non autorisé.'
            ], 403);
        }

        // Vérifiez si l'utilisateur a des classes associées
        $classe = $user->classe;
        if (!$classe) {
            return response()->json([
                'status' => false,
                'message' => 'Aucune classe associée à cet utilisateur.'
            ], 404);
        }

        // Récupérer les soumissions pour le devoir spécifié et la classe associée
        $soumissions = DB::table('soumissions')
            ->join('eleves', 'soumissions.eleve_id', '=', 'eleves.id')
            ->join('users', 'eleves.user_id', '=', 'users.id')
            ->join('devoirs', 'soumissions.devoir_id', '=', 'devoirs.id')
            ->where('soumissions.devoir_id', $devoirId)
            ->where('devoirs.classe_id', $classe->id)
            // ->where('soumissions.soumis', 1)
            ->select(
                'soumissions.id',
                'users.nom as eleve_nom',
                'users.prenom as eleve_prenom',
                'soumissions.soumis',
                'soumissions.dateSoumission',
                'soumissions.commentaire',
                'soumissions.document'
            )
            ->get();

        // Vérifiez si des soumissions existent
        if ($soumissions->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'Aucune soumission trouvée pour ce devoir.'
            ], 404);
        }

        // Retournez les soumissions
        return response()->json([
            'status' => true,
            'soumissions' => $soumissions
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function soumettreDevoir(Request $request, $devoirId)
    {
        $user = auth()->user();

        if (!$user || $user->role !== 'eleve') {
            return response()->json([
                'status' => false,
                'message' => 'Utilisateur non autorise ou non eleve.',
            ], 403);
        }

        $eleve = Eleve::where('user_id', $user->id)->first();

        if (!$eleve) {
            return response()->json([
                'status' => false,
                'message' => 'Élève introuvable.',
            ], 404);
        }

        // Trouver l'entree de soumission correspondante
        $soumission = Soumission::where('devoir_id', $devoirId)->where('eleve_id', $eleve->id)->first();

        if (!$soumission) {
            return response()->json([
                'status' => false,
                'message' => 'Soumission introuvable.',
            ], 404);
        }

        // verifier si le devoir a deja ete soumis
        if ($soumission->soumis) {
            return resonse()->json([
                'status' => false,
                'message' => 'Le devoir a deja ete soumis.',
            ]);
        }

        // Mettre a jour l'etat de soumission
        $soumission->soumis = true;
        $soumission->dateSoumission = now();
        $soumission->save();

        return response()->json([
            'status' => true,
            'message' => 'Devoir soumis aves succes.'
        ]);

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
