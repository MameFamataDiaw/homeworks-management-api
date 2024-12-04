<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Enseignant;
use App\Models\Devoir;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DevoirController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $devoirs = Devoir::all();
        return response()->json([
            'status' => true,
            'devoirs' => $devoirs
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try
        {
            $request->validate(
                [
                    'module' => 'required|string|max:30',
                    'contenu' => 'required|string|max:255',
                    'document' => 'nullable|file|mimes:pdf,docx,jpeg,png,mp4,avi,mov|max:10240',
                    'matiere_id' => 'required|exists:matieres,id'
                ]
            );

            $user = Auth::user();

            if ($user->role !== 'enseignant') {
                return response()->json([
                    'error' => 'Acces non authorise'
                ], 403);
            }

            $classe = $user->classe;

            if (!$classe) {
                return response()->json([
                    'error' => "Vous n'etes pas associe(e) a une classe."
                ], 403);
            }

            // Verification des permissions de l'enseignant
            // $enseignantClasse = $user->classe->id ?? null;
            // if ($request->classe_id != $enseignantClasse) {
            //     return response()->json([
            //         'error ' => 'Vous ne pouvez ajouter un devoir que pour votre classe.'
            //     ], 403);
            // }

            // Gestion de l'upload du fichier
            $filePath = null;
            if ($request->hasFile('document')) {
                try {
                    $file = $request->file('document');
                    $filePath = $file->store('documents/devoirs', 'public');
                } catch (\Exception $e) {
                    return response()->json([
                        'error' => 'Erreur lors de l\'upload du fichier : ' . $e->getMessage()
                    ], 500);
                }
            }

            $devoir = Devoir::create([
                'module' => $request->module,
                'contenu' => $request->contenu,
                'dateAjout' => now(),
                'document' => $filePath,
                'classe_id' => $classe->id,
                'matiere_id' => $request->matiere_id,
            ]);

            return response()->json([
                'status' => true,
                'message' => "Devoir cree !",
                'devoir' => $devoir
            ], 201);

        } catch(\Exception $e){
            \Log::error('Erreur lors de la création du devoir : '.$e->getMessage());
            return response()->json([
                'status' => false,
                'message' => "Erreur lors de la création du devoir.",
                'error' => $e->getMessage()
            ], 400);
        }
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
