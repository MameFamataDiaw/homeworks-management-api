<?php

namespace App\Services;

use App\Mail\AccountCreated;
use Illuminate\Support\Facades\Mail;

class MailService
{
    // public static function sendAccountCreatedEmail($user, $password)
    // {
    //     $details = [
    //         'name' => $user->prenom . ' ' . $user->nom,
    //         'email' => $user->email,
    //         'password' => $password,
    //     ];

    //     Mail::to($user->email)->send(new AccountCreated($details));
    // }

    /**
     * Envoyer un e-mail generique
     * @param string $recipientEmail Adresse du destinataire
     * @param Mailable $mailable Classe representant l'email
     * @return bool
     */
    public function sendMail(string $recipientEmail, $mailable): bool
    {
        try {
            Mail::to($recipientEmail)->send($mailable);
            return true;
        } catch (\Exception $e) {
            // Vous pouvez logger l'erreur ici si nécessaire
            \Log::error('Erreur lors de l\'envoi de l\'e-mail : ' . $e->getMessage());
            return false;
        }
    }
}
