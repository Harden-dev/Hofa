<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demande d'adhésion approuvée</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #28a745;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 0 0 5px 5px;
        }
        .success-icon {
            font-size: 48px;
            margin-bottom: 10px;
        }
        .member-info {
            background-color: white;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #28a745;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            color: #666;
            font-size: 12px;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="success-icon">✅</div>
        <h1>Demande d'adhésion approuvée !</h1>
    </div>

    <div class="content">
        @if($isAdmin)
            <h2>Notification d'approbation</h2>
            <p>Un nouveau membre a été approuvé sur la plateforme.</p>

            <div class="member-info">
                <h3>Informations du membre :</h3>
                <p><strong>Nom :</strong> {{ $member->name }}</p>
                <p><strong>Email :</strong> {{ $member->email }}</p>
                <p><strong>Téléphone :</strong> {{ $member->phone }}</p>
                <p><strong>Type :</strong> {{ $member->type === 'individual' ? 'Individuel' : 'Entreprise' }}</p>
                @if($member->type === 'company' && $member->bossName)
                    <p><strong>Responsable :</strong> {{ $member->bossName }}</p>
                @endif
                <p><strong>Date d'approbation :</strong> {{ $member->approved_at ? $member->approved_at->format('d/m/Y H:i') : 'Maintenant' }}</p>
            </div>
        @else
            <h2>Félicitations {{ $member->name }} !</h2>

            <p>Nous avons le plaisir de vous informer que votre demande d'adhésion a été <strong>approuvée avec succès</strong> !</p>

            @if($customMessage)
                <div class="member-info">
                    <p><strong>Message de l'équipe :</strong></p>
                    <p>{{ $customMessage }}</p>
                </div>
            @endif

            <p>Vous êtes maintenant membre actif de notre communauté et vous pouvez :</p>
            <ul>
                <li>Accéder à toutes les fonctionnalités de la plateforme</li>
                <li>Participer aux activités et événements</li>
                <li>Bénéficier de nos services</li>
                <li>Interagir avec les autres membres</li>
            </ul>

            <div class="member-info">
                <h3>Vos informations :</h3>
                <p><strong>Nom :</strong> {{ $member->name }}</p>
                <p><strong>Email :</strong> {{ $member->email }}</p>
                <p><strong>Statut :</strong> <span style="color: #28a745;">✅ Actif</span></p>
                <p><strong>Date d'approbation :</strong> {{ $member->approved_at ? $member->approved_at->format('d/m/Y H:i') : 'Maintenant' }}</p>
            </div>

            <p>Si vous avez des questions ou besoin d'aide, n'hésitez pas à nous contacter.</p>

            <p>Bienvenue dans notre communauté !</p>
        @endif
    </div>

    <div class="footer">
        <p>Cet email a été envoyé automatiquement par {{ config('app.name') }}</p>
        <p>© {{ date('Y') }} {{ config('app.name') }}. Tous droits réservés.</p>
    </div>
</body>
</html>
