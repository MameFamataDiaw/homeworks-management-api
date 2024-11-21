<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compte Élève Créé</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f8f9fa;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #0056b3;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #0056b3;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
        .button:hover {
            background-color: #004494;
        }
        .footer {
            margin-top: 20px;
            font-size: 0.9em;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Bonjour {{ $details['parent_name'] }},</h1>
        <p>Le compte de votre enfant a été créé avec succès sur la plateforme <strong>Homework Management</strong>.</p>
        <h2>Informations de connexion de l'élève :</h2>
        <p><strong>Prénom et nom :</strong> {{ $details['eleve_name'] }}</p>
        <p><strong>Email :</strong> {{ $details['email'] }}</p>
        <p><strong>Mot de passe temporaire :</strong> {{ $details['password'] }}</p>
        <p>Nous vous recommandons de changer le mot de passe de votre enfant lors de sa première connexion.</p>
        <p>Vous pouvez aider votre enfant à se connecter en cliquant sur le lien ci-dessous :</p>
        <p><a href="{{ url('/') }}" class="button">Se connecter à la plateforme</a></p>
        <p>Merci de faire confiance à notre plateforme.</p>
        <div class="footer">
            <p><em>- L'équipe Homework Management</em></p>
        </div>
    </div>
</body>
</html>
