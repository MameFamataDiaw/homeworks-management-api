<?php

namespace App\Http\Controllers;

use App\Models\Matiere;
use Illuminate\Http\Request;

class MatiereController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $matieres = Matiere::all();
        return response()->json([
            'status' => true,
            'matieres' => $matieres
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
                    'nomMatiere' => 'required|string|unique:matieres,nomMatiere|max:20',
                ]
            );

            $matiere = Matiere::create([
                'nomMatiere' => $request->nomMatiere,

            ]);

            return response()->json([
                'status' => true,
                'message' => "Matiere cree avec succes !",
                'matiere' => $matiere
            ],201);
        }catch(\Exception $e){
            \Log::error('Erreur lors de la création de la matiere : '.$e->getMessage());
            return response()->json([
                'status' => false,
                'message' => "Erreur lors de la création de la matiere.",
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $matiere = Matiere::findOrFail($id);
            return response()->json($matiere, 200);
        } catch (ModelNotFoundException $ex) {
            return response()->json(['error' => 'Matiere non trouvee'], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $matiere = Matiere::findOrFail($id);
            $request->validate(
                [
                    'nomMatiere' => 'required|string|unique:matieres,nomMatiere|max:20',
                ]
            );
            
            // Mettre à jour les autres champs
            $matiere->update([
                'nomMatiere' => $request->nomMatiere,
            ]);

            return response()->json([
                'status' => true,
                'message' => "Matiere updated successfully !",
                'matiere' => $matiere
            ], 200);

        } catch (ModelNotFoundException $ex) {
            \Log::error('Matiere non trouvée pour mise à jour : '.$ex->getMessage());
            return response()->json(['error' => 'Matiere non trouvée'], 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $matiere = Matiere::findOrFail($id);
            $matiere->delete();
            return response()->json(null,204);
        }catch(ModelNotFoundException $ex){
            return response()->json(['error' => 'Matiere non trouvée'], 404);
        }
    }
}
