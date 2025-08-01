<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réponse à votre demande d'adhésion</title>
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
            background-color: #dc3545;
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
        .reject-icon {
            font-size: 48px;
            margin-bottom: 10px;
        }
        .member-info {
            background-color: white;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #dc3545;
        }
        .reason-box {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
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
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="reject-icon">❌</div>
        <h1>Réponse à votre demande d'adhésion</h1>
    </div>

    <div class="content">
        @if($isAdmin)
            <h2>Notification de rejet</h2>
            <p>Un membre a été rejeté sur la plateforme.</p>

            <div class="member-info">
                <h3>Informations du membre :</h3>
                <p><strong>Nom :</strong> {{ $member->name }}</p>
                <p><strong>Email :</strong> {{ $member->email }}</p>
                <p><strong>Téléphone :</strong> {{ $member->phone }}</p>
                <p><strong>Type :</strong> {{ $member->type === 'individual' ? 'Individuel' : 'Entreprise' }}</p>
                @if($member->type === 'company' && $member->bossName)
                    <p><strong>Responsable :</strong> {{ $member->bossName }}</p>
                @endif
                <p><strong>Date de rejet :</strong> {{ $member->rejected_at ? $member->rejected_at->format('d/m/Y H:i') : 'Maintenant' }}</p>
            </div>

            @if($reason)
                <div class="reason-box">
                    <h4>Raison du rejet :</h4>
                    <p>{{ $reason }}</p>
                </div>
            @endif
        @else
            <h2>Bonjour {{ $member->name }},</h2>

            <p>Nous avons examiné votre demande d'adhésion avec attention et nous regrettons de vous informer que nous ne pouvons pas l'accepter pour le moment.</p>

            @if($reason)
                <div class="reason-box">
                    <h4>Raison du rejet :</h4>
                    <p>{{ $reason }}</p>
                </div>
            @endif

            @if($customMessage)
                <div class="member-info">
                    <p><strong>Message de l'équipe :</strong></p>
                    <p>{{ $customMessage }}</p>
                </div>
            @endif

            <p>Nous vous encourageons à :</p>
            <ul>
                <li>Réviser votre demande en tenant compte des points mentionnés</li>
                <li>Fournir des informations plus complètes si nécessaire</li>
                <li>Nous recontacter si vous avez des questions</li>
            </ul>

            <div class="member-info">
                <h3>Vos informations :</h3>
                <p><strong>Nom :</strong> {{ $member->name }}</p>
                <p><strong>Email :</strong> {{ $member->email }}</p>
                <p><strong>Statut :</strong> <span style="color: #dc3545;">❌ Rejeté</span></p>
                <p><strong>Date de rejet :</strong> {{ $member->rejected_at ? $member->rejected_at->format('d/m/Y H:i') : 'Maintenant' }}</p>
            </div>

            <p>Si vous souhaitez soumettre une nouvelle demande à l'avenir, nous serons ravis de l'examiner.</p>

            <p>Nous vous remercions de votre compréhension et de l'intérêt que vous portez à notre organisation.</p>
        @endif
    </div>

    <div class="footer">
        <p>Cet email a été envoyé automatiquement par {{ config('app.name') }}</p>
        <p>© {{ date('Y') }} {{ config('app.name') }}. Tous droits réservés.</p>
    </div>
</body>
</html>
