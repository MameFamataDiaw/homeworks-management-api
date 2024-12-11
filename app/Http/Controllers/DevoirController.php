<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Enseignant;
use App\Models\Devoir;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Mail\DevoirAssigned;
use App\Services\MailService;

class DevoirController extends Controller
{
    protected $mailService;

    public function __construct(MailService $mailService)
    {
        $this->mailService = $mailService;
    }
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

    public function assignDevoir(Request $request, $devoirId)
    {
        $user = Auth::user();

        if($user->role !== 'enseignant') {
            return response()->json([
                'status' => false,
                'message' => 'Acces non autorise.'
            ], 403);
        }

        $devoir = Devoir::findOrFail($devoirId);

        $classe = $user->classe;

        if (!$classe || $devoir->classe_id !== $classe->id) {
            return response()->json([
                'status' => false,
                'message' => 'Ce devoir ne peut etre assigne.'
            ], 403);
        }

        $eleves = $classe->eleves;

        if ($eleves->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'Aucun eleve dans cette classe.'
            ], 404);
        }

        $request->validate([
            'dateSoumission' => 'required|date|after:today',
        ]);
        $dateSoumission = $request->dateSoumission;

        // Regrouper les données à insérer dans la table pivot
        $now = now();
        $data = [];

        foreach ($eleves as $eleve) {
           $data[$eleve->id] = [
            'dateAttribution' => $now,
            'dateSoumission' => $dateSoumission,
            'soumis' => false,
           ];

            // Details du mail
            $parent = $eleve->parent;
            if ($parent && $parent->email) {
                $details = [
                    'nom_eleve' => $eleve->prenom . ' ' . $eleve->nom,
                    'nom_classe' => $classe->nomClasse,
                    'module' => $devoir->module,
                    'dateSoumission' => $dateSoumission
                ];

                // Envoyer l'email au parent
                try {
                    $this->mailService->sendMail($parent->email, new DevoirAssigned($details));
                } catch (\Exception $e) {
                    \Log::error("Erreur lors de l'envoi de l'e-mail à " . $parent->email . ": " . $e->getMessage());
                }
            }
            if (!$parent || !$parent->email) {
                \Log::info("Pas d'e-mail pour le parent de l'élève : " . $eleve->id);
                continue;
            }
        }
        // foreach ($eleves as $eleve) {
        //     // Détails du mail
        //     $parent = $eleve->parent;  // Vérifie si l'élève a un parent
        //     if ($parent && $parent->user && $parent->user->email) {
        //         $details = [
        //             'nom_eleve' => $eleve->prenom . ' ' . $eleve->nom,
        //             'nom_classe' => $classe->nomClasse,
        //             'module' => $devoir->module,
        //             'dateSoumission' => $dateSoumission
        //         ];

        //         // Envoyer l'email au parent
        //         try {
        //             $this->mailService->sendMail($parent->user->email, new DevoirAssigned($details));  // Utilisez $parent->user->email
        //         } catch (\Exception $e) {
        //             \Log::error("Erreur lors de l'envoi de l'e-mail à " . $parent->user->email . ": " . $e->getMessage());
        //         }
        //     } else {
        //         \Log::info("Pas d'e-mail pour le parent de l'élève : " . $eleve->id);
        //     }
        // }


        // Assigner le devoir a chaque eleve
        $devoir->eleves()->syncWithoutDetaching($data);

        \Log::info("Assignation du devoir ID: " . $devoir->id . " à la classe ID: " . $classe->id);


        return response()->json([
            'status' => true,
            'message' => 'Le devoir a ete assigne avec succes et un email est envoye aux parents.',
            'devoir' => $devoir->module,
            'data' => $data
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
