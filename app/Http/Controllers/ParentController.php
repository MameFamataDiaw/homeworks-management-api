<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Parentt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Mail\AccountCreated;
use App\Services\MailService;

class ParentController extends Controller
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
        //
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
                    'prenom' => 'required|string|max:30',
                    'nom' => 'required|string|max:30',
                    'genre' => 'required|in:masculin,feminin',
                    'telephone' => 'required|string|unique:parentts,telephone|min:9|max:20',
                    'email' => 'required|email|unique:users,email',
                    'password' => 'required|min:8',
                ]
            );

            $user = User::create([
                'prenom' => $request->prenom,
                'nom' => $request->nom,
                'genre' => $request->genre,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'role' => 'parent',
            ]);

            $parent = Parentt::create([
                'user_id' => $user->id,
                'telephone' => $request->telephone
            ]);



            // Envoie de l'email
            // MailService::sendAccountCreatedEmail($user, $request->password);

            $detais = [
                'name' => $user->prenom . ' ' . $user->nom,
                'email' => $user->email,
                'password' => $user->password,
            ];

            $this->mailService->sendMail($request->email, new AccountCreated($detais));

            return response()->json([
                'status' => true,
                'message' => "Compte parent créé avec succès.",
                'user' => $user,
                'parent' => $parent,
            ],201);
        }catch(\Exception $e){
            \Log::error('Erreur lors de la création du compte : '.$e->getMessage());
            return response()->json([
                'status' => false,
                'message' => "Erreur lors de la création du compte.",
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function getEnfants(Request $request)
    {
        $user = Auth::user();

        if ($user->role !== 'parent') {
            return response()->json([
                'message' => 'Acces non autorise'
            ], 403);
        }

        // Récupérer le parent lié à l'utilisateur
        $parent = Parentt::where('user_id', $user->id)->first();

        if (!$parent) {
            return response()->json([
                'message' => 'Parent introuvable'
            ], 404);
        }

        // Recuperer les enfants du parent et leurs classes
        $enfants = $parent->enfants()->with('classe')->get();

        $formattedEnfants = $enfants->map(function ($enfant) {
            return [
                'enfant' => $enfant->user->prenom . ' ' . $enfant->user->nom, // Nom de l'élève (relation avec User)
                'classe' => $enfant->classe->nomClasse, // Nom de la classe (relation avec Classe)
            ];
        });

        return response()->json([
            'status' => true,
            'data' => $formattedEnfants,  // Envoie juste la liste des enfants
        ]);
    }

    public function getDevoirsAssignes(Request $request)
    {
        $user = Auth::user();

        // Vérifier si l'utilisateur est un parent
        if ($user->role !== 'parent') {
            return response()->json([
                'message' => 'Accès non autorisé.',
            ], 403);
        }

        // Récupérer le parent et ses enfants
        $parent = $user->parent;
        $enfants = $parent->enfants()->with('devoirsAssignes.matiere', 'classe')->get();

        // Vérifier s'il y a des enfants liés
        if ($enfants->isEmpty()) {
            return response()->json([
                'message' => 'Aucun enfant n\'est associé à ce parent.',
            ], 404);
        }

        // Récupérer l'identifiant de l'enfant depuis la requête
        $enfantId = $request->input('enfant_id');
        $enfant = $enfants->firstWhere('id', $enfantId) ?: $enfants->first();

        // Vérifier si l'enfant spécifié existe
        if (!$enfant) {
            return response()->json([
                'message' => 'Enfant introuvable.',
            ], 404);
        }

        // Récupérer les devoirs assignés à cet enfant
        $devoirs = $enfant->devoirsAssignes->map(function ($devoir) {
            return [
                'matiere' => $devoir->matiere->nomMatiere,
                'module' => $devoir->module,
                'date_attribution' => $devoir->pivot->dateAttribution,
                'date_soumission' => $devoir->pivot->dateSoumission,
                'soumis' => $devoir->pivot->soumis,
                'note' => $devoir->pivot->note,
                'commentaire' => $devoir->pivot->commentaire,
            ];
        });

        // Calcul des statistiques
        $stats = [
            'total' => $devoirs->count(),
            'soumis' => $devoirs->where('soumis', true)->count(),
            'non_soumis' => $devoirs->where('soumis', false)->count(),
        ];

        // Ajouter les informations sur la classe de l'enfant
        $classe = $enfant->classe ? [
            'id' => $enfant->classe->id,
            'nomClasse' => $enfant->classe->nomClasse,
        ] : null;

        // Retourner les données
        return response()->json([
            'status' => true,
            'message' => "Devoirs assignés à l'enfant récupérés avec succès.",
            'data' => [
                'enfant' => $enfant->user->prenom . ' ' . $enfant->user->nom,
                'classe' => $classe,
                'devoirs' => $devoirs,
                'stats' => $stats,
            ],
        ], 200);
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
