<?php

namespace App\Http\Controllers;

use App\Models\Classe;
use http\Env\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use DB;

class ClasseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $classes = Classe::all();
        return response()->json([
            'status' => true,
            'classes' => $classes
        ]);
    }

    public function getClasses(Request $request)
    {
        $query = $request->get('query');

        $data = DB::table('classes');

        if (!is_null($query)) {
            $classes = $data->where('nomClasse','like','%'.$query.'%');

            return response($classes->paginate(10),200);
        }

        return response($data->paginate(10),200);
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
                    'nomClasse' => 'required|string|unique:classes,nomClasse|max:10',
                    'niveau' => 'required|string|max:50',
                ]
            );

            $classe = Classe::create([
                'nomClasse' => $request->nomClasse,
                'niveau' => $request->niveau,

            ]);

            return response()->json([
                'status' => true,
                'message' => "Classe cree avec succes !",
                'classe' => $classe
            ],201);
        }catch(\Exception $e){
            \Log::error('Erreur lors de la création de la classe : '.$e->getMessage());
            return response()->json([
                'status' => false,
                'message' => "Erreur lors de la création de la classe.",
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
            $classe = Classe::findOrFail($id);
            return response()->json($classe, 200);
        } catch (ModelNotFoundException $ex) {
            return response()->json(['error' => 'Classe non trouvee'], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $classe = Classe::findOrFail($id);
            $request->validate(
                [
                    'nomClasse' => 'required|string|unique:classes,nomClasse|max:10',
                    'niveau' => 'required|string|max:20',
                ]
            );

            // Mettre à jour les autres champs
            $classe->update([
                'nomClasse' => $request->nomClasse,
                'niveau' => $request->niveau,
            ]);

            return response()->json([
                'status' => true,
                'message' => "Classe updated successfully !",
                'classe' => $classe
            ], 200);

        } catch (ModelNotFoundException $ex) {
            \Log::error('Classe non trouvée pour mise à jour : '.$ex->getMessage());
            return response()->json(['error' => 'Classe non trouvée'], 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $classe = Classe::findOrFail($id);
            $classe->delete();
            return response()->json(null,204);
        }catch(ModelNotFoundException $ex){
            return response()->json(['error' => 'Classe non trouvée'], 404);
        }
    }

    /**
     * Assign a subject to a class
     */
    public function assignSubjectsToClass (Request $request, $classeId) {
        $validated = $request->validate([
            'matiere_ids' => 'required|array',
            'matiere_ids.*' => 'exists:matieres,id',
        ]);

        $classe = Classe::findOrFail($classeId);

        //Associer a matiere a la classe
        $classe->matieres()->sync($validated['matiere_ids']);

        return response()->json([
            'message' => 'Matiere attribuèe à classe.',
            'classe' => $classe->load('matieres'),
        ]);
    }


}
