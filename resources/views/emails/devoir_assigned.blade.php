<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Devoir Assigné</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            max-width: 600px;
            margin: 0 auto;
        }
        .header {
            background-color: #f4f4f4;
            padding: 10px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }
        .content {
            margin-top: 20px;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 0.9em;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Nouveau Devoir pour {{ $details['nom_eleve'] }}</h2>
        </div>
        <div class="content">
            <p>Bonjour,</p>
            <p>Un nouveau devoir a été attribué à votre enfant <strong>{{ $details['nom_eleve'] }}</strong>, dans la classe <strong>{{ $details['nom_classe'] }}</strong>.</p>
            <p>
                <strong>Détails du devoir :</strong><br>
                - Module : {{ $details['module'] }}<br>
                - Date limite de soumission : {{ $details['dateSoumission'] }}<br>
            </p>
            <p>Veuillez vous assurer que le devoir est soumis à temps.</p>
            <p>Merci,</p>
            <p><em>L'équipe pédagogique</em></p>
        </div>
        <div class="footer">
            <p>Ce message est généré automatiquement, veuillez ne pas y répondre.</p>
        </div>
    </div>
</body>
</html>
