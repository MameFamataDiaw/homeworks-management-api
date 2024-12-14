<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Parentt;
use App\Models\Eleve;
use App\Models\Classe;
use App\Models\Devoir;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Mail\StudentAccountCreated;
use App\Services\MailService;
use DB;

class EleveController extends Controller
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
        $eleves = Eleve::all();
        return response()->json([
            'status' => true,
            'eleves' => $eleves
        ]);
    }

    public function getElevesByClasse(Request $request)
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
        $classes = $user->classe()->with('eleves')->get();

        // Compiler toutes les eleves associees
        $eleves = $classes->flatMap(function ($classe) {
            return $classe->eleves;
        })->unique('id');

        if (!is_null($query)) {
            $eleves = $eleves->filter(function ($eleve) use ($query) {
                return stripos($eleve->prenom, $query) !== false || stripos($eleve->nom, $query) !== false;
            });
        }

        // paginer les resultats
        $page = $request->get('page', 1);
        $perPage = 10;
        $paginated = $eleves->slice(($page - 1) * $perPage, $perPage);


        return response()->json([
            'status' => true,
            'data' => $paginated,
            'total' => $eleves->count(),
            'current_page' => $page,
            'last_page' => ceil($eleves->count() / $perPage),
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
                    'prenom' => 'required|string|max:30',
                    'nom' => 'required|string|max:30',
                    'genre' => 'required|in:masculin,feminin',
                    'email' => 'required|email|unique:users,email',
                    'password' => 'required|min:8',
                    'email_parent' => 'required|exists:users,email',
                    'nom_classe' => 'required|exists:classes,nomClasse',
                ]
            );

            // Faites plutôt
            $parent_user = User::where('email', $request->email_parent)->first();
            if (!$parent_user) {
                return response()->json([
                    'status' => false,
                    'message' => "Parent non trouvé avec cet email.",
                ], 400);
            }

            // Récupérer l'enregistrement correspondant dans la table parentts
            $parent = Parentt::where('user_id', $parent_user->id)->first();
            if (!$parent) {
                return response()->json([
                    'status' => false,
                    'message' => "Enregistrement parent non trouvé.",
                ], 400);
            }

            $parent_id = $parent->id; // Ceci sera l'ID de la table parentts

            // // Récupérer l'ID du parent en fonction de l'email
            // $parent = User::where('email', $request->email_parent)->first();
            // if (!$parent) {
            //     return response()->json([
            //         'status' => false,
            //         'message' => "Parent non trouvé avec cet email.",
            //     ], 400);
            // }

            // $parent_id = $parent->id; // Récupérer l'ID du parent

        // Récupérer l'ID de la classe en fonction du nom
        $classe = Classe::where('nomClasse', $request->nom_classe)->first();
        if (!$classe) {
            return response()->json([
                'status' => false,
                'message' => "Classe non trouvée avec ce nom.",
            ], 400);
        }

        $classe_id = $classe->id; // Récupérer l'ID de la classe


        // $tempPassword = 'passer@123';

            // Creer l'utilisateur
            $user = User::create([
                'prenom' => $request->prenom,
                'nom' => $request->nom,
                'genre' => $request->genre,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'role' => 'eleve',
            ]);

            // Associer l'élève à un utilisateur, un parent et une classe
            $eleve = Eleve::create([
                'user_id' => $user->id,
                'parentt_id' => $parent_id,
                'classe_id' => $classe_id,
            ]);


            // // Récupérer les devoirs assignés à la classe
            // $devoirs = Devoir::where('classe_id', $validated['classe_id'])->get();

            // // Ajouter les devoirs pour cet élève dans la table `soumissions`
            // foreach ($devoirs as $devoir) {
            //     DB::table('soumissions')->insert([
            //         'devoir_id' => $devoir->id,
            //         'eleve_id' => $eleve->user_id,
            //         'dateAttribution' => now(),
            //         'dateSoumission' => null,
            //         'soumis' => false,
            //         'created_at' => now(),
            //         'updated_at' => now(),
            //     ]);
            // }

            // Incrementer l'effectif de la classe
            $classe->increment('effectif');
            // // Utiliser increment au lieu de le faire manuellement
            // $classe->effectif += 1
            // $classe->save().

            // Preparer les details pour l'e-mail
            $detais = [
                'eleve_name' => $user->prenom . ' ' . $user->nom,
                'email' => $user->email,
                'password' => $request->password, //le mot de passe n'est pas crypte
                'parent_name' => $parent->prenom . ' ' . $parent->nom, // Ajoutez le nom du parent

            ];

            // Envoyer l'email au parent
            $this->mailService->sendMail($request->email_parent, new StudentAccountCreated($detais));

            return response()->json([
                'status' => true,
                'message' => "Compte éléve créé et email envoyé au parent.",
                'user' => $user,
                'eleve' => $eleve,
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

    public function getDevoirsAfaire(Request $request)
    {
        $user = auth()->user();

        if (!$user || $user->role !== 'eleve') {
            return response()->json([
                'status' => false,
                'message' => 'Utilisateur non autorise ou non eleve.'
            ], 403);
        }

        $eleve = Eleve::where('user_id', $user->id)->with('devoirsAssignes')->first();


        if (!$eleve) {
            return response()->json([
                'status' => false,
                'message' => 'Eleve introuvable.',
            ]);
        }

        $devoirsAfaire = $eleve->devoirsAssignes->map(function ($devoir) {
            return [
                'devoir_id' => $devoir->pivot->devoir_id,
                'matiere' => $devoir->matiere->nomMatiere,
                'module' => $devoir->module,
                'dateAttribution' => $devoir->pivot->dateAttribution,
                'aRendre' => $devoir->pivot->aRendre,
                'soumis' => $devoir->pivot->soumis,
                'note' => $devoir->pivot->note,
                'commentaire' => $devoir->pivot->commentaire,
            ];
        });

        if ($devoirsAfaire->isEmpty()) {
            return response()->json([
                'status' => true,
                'message' => 'Aucun devoir assigne a cet eleve.',
                'devoirs' => [],
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Devoirs a faire recuperes.',
            'devoirs' => $devoirsAfaire,
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
        try {
            $eleve = Eleve::findOrFail($id);
            $classe = $eleve->classe;

            if ($classe) {
                $classe->decrement('effectif');
            }

            $eleve->delete();

            return response()->json([
                'status' => true,
                'message' => "L'eleve a ete supprime avec succes."
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la suppression de l\'eveve : ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => "Erreur lors de la suppression de l'eleve.",
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    public function transferStudent(Request $request, $eleveId)
    {
        $request->validate([
            'new_class' => 'required|exists:classes,nomClasse',
        ]);

        try {
            $eleve = Eleve::findOrFail($eleveId);
            $oldClass = $eleve->classe;
            $newClass = Classe::where('nomClasse', $request->new_class)->first();

            if (!$newClasse) {
                return response()->json([
                    'status' => false,
                    'message' => "La nouvelle classe n'a pas ete trouvee.",
                ], 400);
            }

            // Decrementer l'effectif de l'ancienne classe si elle existe.
            if ($oldClass) {
                $oldClass->decrement('effectif');
            }

            // Incrementer l'effectif de la nouvelle classe
            $newClass->increment('effectif');

            // Mettre a jour la classe de l'eleve
            $eleve->classe_id = $newClass->id;
            $eleve->save();

            return response()->json([
                'status' => true,
                'message' => "L'élève a été transféré avec succès.",
                'eleve' => $eleve,
                'ancienne_classe' => $oldClass ? $oldClass->nomClasse : null,
                'nouvelle_classe' => $newClass->nomClasse,
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Erreur lors du transfert de l\'élève : ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => "Erreur lors du transfert de l'élève.",
                'error' => $e->getMessage(),
            ], 400);
        }
    }
}
