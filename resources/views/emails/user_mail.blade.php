<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Bienvenue sur la plateforme de HOFA</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .header {
            background-color: #28a745;
            color: #ffffff;
            padding: 20px;
            border-radius: 8px 8px 0 0;
            text-align: center;
        }

        .content {
            padding: 20px;
        }

        .credentials {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }

        .footer {
            background-color: #f8f9fa;
            padding: 15px;
            text-align: center;
            border-radius: 0 0 8px 8px;
            color: #666;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Bienvenue {{ $user->name }} !</h1>
        </div>

        <div class="content">
            <p>Bonjour {{ $user->name }},</p>

            @if($isNewUser)
                <p>Vous avez été ajouté à la plateforme de HOFA. Voici vos identifiants de connexion :</p>
            @else
                <p>Vos identifiants ont été mis à jour :</p>
            @endif

            <div class="credentials">
                <p><strong>Email :</strong> {{ $user->email }}</p>
                <p><strong>Mot de passe temporaire :</strong> {{ $password }}</p>
                <p><strong>Rôle :</strong> {{ ucfirst($user->role === 'admin' ? 'Administrateur' : 'Utilisateur') }}</p>
            </div>

            <p>Veuillez utiliser ces identifiants pour vous connecter à la plateforme.</p>

            @if($isNewUser)
                <p><em>Pour votre sécurité, nous vous recommandons de changer votre mot de passe lors de votre première connexion.</em></p>
            @endif

            <p>Merci de faire partie de l'équipe HOFA !</p>
        </div>

        <div class="footer">
            <p>L'équipe de HOFA</p>
        </div>
    </div>
</body>

</html>
