<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Enseignant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Mail\AccountCreated;
use App\Services\MailService;

class EnseignantController extends Controller
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
                    'telephone' => 'required|string|unique:enseignants,telephone|min:9|max:20',
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
                'role' => 'enseignant',
            ]);

            $enseignant = Enseignant::create([
                'user_id' => $user->id,
                'telephone' => $request->telephone
            ]);



            $detais = [
                'name' => $user->prenom . ' ' . $user->nom,
                'email' => $user->email,
                'password' => $user->password,
            ];

            $this->mailService->sendMail($request->email, new AccountCreated($detais));

            return response()->json([
                'status' => true,
                'message' => "Compte enseignant créé avec succès.",
                'user' => $user,
                'enseignant' => $enseignant,
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
