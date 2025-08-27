<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Réinitialisation de mot de passe - HOFA</title>
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
            background-color: #dc3545;
            color: #ffffff;
            padding: 20px;
            border-radius: 8px 8px 0 0;
            text-align: center;
        }

        .content {
            padding: 20px;
        }

        .credentials {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }

        .warning {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
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

        .password {
            font-family: 'Courier New', monospace;
            font-size: 18px;
            font-weight: bold;
            color: #dc3545;
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
            text-align: center;
            letter-spacing: 2px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Réinitialisation de mot de passe</h1>
        </div>

        <div class="content">
            <p>Bonjour {{ $user->name }},</p>

            <p>Nous avons reçu une demande de réinitialisation de mot de passe pour votre compte HOFA.</p>

            <p>Voici votre nouveau mot de passe temporaire :</p>

            <div class="credentials">
                <p><strong>Email :</strong> {{ $user->email }}</p>
                <p><strong>Mot de passe temporaire :</strong></p>
                <div class="password">{{ $temporaryPassword }}</div>
            </div>

            <div class="warning">
                <p><strong>⚠️ Important :</strong></p>
                <ul>
                    <li>Ce mot de passe est temporaire et valide pour une seule connexion</li>
                    <li>Vous devrez changer ce mot de passe lors de votre prochaine connexion</li>
                    <li>Ne partagez jamais ce mot de passe avec qui que ce soit</li>
                </ul>
            </div>

            <p>Utilisez ce mot de passe temporaire pour vous connecter à votre compte, puis changez-le immédiatement pour un nouveau mot de passe sécurisé.</p>

            <p>Si vous n'avez pas demandé cette réinitialisation, veuillez ignorer cet email ou contacter notre équipe de support.</p>

            <p>Cordialement,<br>L'équipe HOFA</p>
        </div>

        <div class="footer">
            <p>Cet email a été envoyé automatiquement. Veuillez ne pas y répondre.</p>
            <p>© {{ date('Y') }} HOFA - Tous droits réservés</p>
        </div>
    </div>
</body>

</html>
