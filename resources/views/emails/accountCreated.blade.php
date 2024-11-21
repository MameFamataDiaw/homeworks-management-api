<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Création de compte</title>
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
        <h1>Bonjour {{ $details['name'] }},</h1>
        <p>Nous sommes heureux de vous informer que votre compte sur la plateforme <strong>Homework Management</strong> a été créé avec succès.</p>
        <p>Voici vos informations personnelles :</p>
        <ul>
            <li><strong>Prénom et Nom :</strong> {{ $details['name'] }}</li>
            <li><strong>Email :</strong> {{ $details['email'] }}</li>
            <li><strong>Mot de passe temporaire :</strong> {{ $details['password'] }}</li>
        </ul>
        <p>Veuillez cliquer sur le bouton ci-dessous pour vous connecter à votre compte :</p>
        <p>
            <a href="{{ url('/') }}" class="button">Se connecter</a>
        </p>
        <p>Pour des raisons de sécurité, nous vous recommandons de changer votre mot de passe dès votre première connexion.</p>
        <div class="footer">
            <p>Merci d'utiliser notre plateforme. Si vous avez des questions, n'hésitez pas à nous contacter.</p>
        </div>
    </div>
</body>
</html>
