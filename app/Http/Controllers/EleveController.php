<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Parentt;
use App\Models\Eleve;
use App\Models\Classe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Mail\StudentAccountCreated;
use App\Services\MailService;

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
                    'email_eleve' => 'required|email|unique:users,email',
                    'password' => 'required|min:8',
                    'email_parent' => 'required|exists:users,email',
                    'nom_classe' => 'required|exists:classes,nomClasse',
                ]
            );

            // Récupérer l'ID du parent en fonction de l'email
        $parent = User::where('email', $request->email_parent)->first();
        if (!$parent) {
            return response()->json([
                'status' => false,
                'message' => "Parent non trouvé avec cet email.",
            ], 400);
        }

        $parent_id = $parent->id; // Récupérer l'ID du parent

        // Récupérer l'ID de la classe en fonction du nom
        $classe = Classe::where('nomClasse', $request->nom_classe)->first();
        if (!$classe) {
            return response()->json([
                'status' => false,
                'message' => "Classe non trouvée avec ce nom.",
            ], 400);
        }

        $classe_id = $classe->id; // Récupérer l'ID de la classe


        $tempPassword = 'passer@123';

            $user = User::create([
                'prenom' => $request->prenom,
                'nom' => $request->nom,
                'genre' => $request->genre,
                'email' => $request->email_eleve,
                'password' => bcrypt($request->$tempPassword),
                'role' => 'eleve',
            ]);

            $eleve = Eleve::create([
                'user_id' => $user->id,
                'parentt_id' => $parent_id,
                'classe_id' => $classe_id,
            ]);


            $detais = [
                'eleve_name' => $user->prenom . ' ' . $user->nom,
                'email' => $user->email,
                'password' => $tempPassword,
                'parent_name' => $parent->prenom . ' ' . $parent->nom, // Ajoutez le nom du parent

            ];

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
