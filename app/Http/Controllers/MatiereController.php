<?php

namespace App\Http\Controllers;

use App\Models\Matiere;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use DB;

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


    public function getMatieres(Request $request)
    {
        $query = $request->get('query');

        $data = DB::table('matieres');

        if (!is_null($query)) {
            $matieres = $data->where('nomMatiere','like','%'.$query.'%');

            return response($matieres->paginate(10),200);
        }

        return response($data->paginate(10),200);
    }

    public function getMatieresByClasse(Request $request)
    {
        $query = $request->get('query');
        $user = Auth::user();
        //auth()->user();

        if ($user->role !== 'enseignant') {
            return response()->json([
                'status' => false,
                'message' => 'Vous n\'etes pas autorise a acceder a cette ressource.',
                'role_detected' => $user->role
            ], 403);
        }

        // Recuperer la classes enseignes par cette enseignant
        $classes = $user->classe()->with('matieres')->get();

        // Compiler toutes les matieres associees
        $matieres = $classes->flatMap(function ($classe) {
            return $classe->matieres;
        })->unique('id');

        if (!is_null($query)) {
            $matieres = $matieres->filter(function ($matiere) use ($query) {
                return stripos($matiere->nomMatiere, $query) !== false;
            });
        }

        // paginer les resultats
        $page = $request->get('page', 1);
        $perPage = 10;
        $paginated = $matieres->slice(($page - 1) * $perPage, $perPage);


        return response()->json([
            'status' => true,
            'data' => $paginated,
            'total' => $matieres->count(),
            'current_page' => $page,
            'last_page' => ceil($matieres->count() / $perPage),
        ]);
    }

    // public function getMatieresByClasse(Request $request)
    // {
    //     $user = Auth::user();

    //     // Vérifier si l'utilisateur est bien un enseignant
    //     if ($user->role !== 'enseignant') {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Vous n\'êtes pas autorisé à accéder à cette ressource.',
    //             'role_detected' => $user->role,
    //             'role_expected' => 'enseignant',
    //         ], 403);
    //     }

    //     // Récupérer la classe de l'enseignant
    //     $classe = $user->classe;
    //     if (!$classe) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Aucune classe assignée à cet enseignant.',
    //         ], 404);
    //     }

    //     // Récupérer les matières associées à cette classe
    //     $query = $request->get('query');
    //     $matieres = $classe->matieres();

    //     if ($query) {
    //         $matieres = $matieres->where('nomMatiere', 'LIKE', "%{$query}%");
    //     }

    //     $matieres = $matieres->get();

    //     return response()->json([
    //         'status' => true,
    //         'data' => $matieres,
    //     ]);
    // }



    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try
        {
            $request->validate(
                [
                    'nomMatiere' => 'required|string|unique:matieres,nomMatiere|max:100',
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
