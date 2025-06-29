<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Inscription confirmée</title>
</head>

<body style="background-color: #f3f4f6; font-family: 'Montserrat', Arial, sans-serif; padding: 20px;">
    <div
        style="max-width: 600px; margin: auto; background-color: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.05); font-family: 'Montserrat', Arial, sans-serif;">

        <div style="background-color: #10b981; color: white; padding: 20px;">
            <h2 style="margin: 0;">
                @if ($isAdmin)
                    👤 Nouveau membre inscrit
                @else
                    🎉 Bienvenue sur {{ config('app.name') }} !
                @endif
            </h2>
        </div>

        <div style="padding: 24px; color: #111827; font-size: 14px;">
            @if ($isAdmin)
                <p style="margin-bottom: 16px;">Un nouvel utilisateur s'est inscrit :</p>

                <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                    <tbody>
                        <tr style="background-color: #f9fafb;">
                            <td style="padding: 8px;"><strong>Nom</strong></td>
                            <td style="padding: 8px;">{{ $member->name }}</td>
                        </tr>
                        <tr>
                            <td style="padding: 8px;"><strong>Email</strong></td>
                            <td style="padding: 8px;">{{ $member->email }}</td>
                        </tr>
                        <tr style="background-color: #f9fafb;">
                            <td style="padding: 8px;"><strong>Téléphone</strong></td>
                            <td style="padding: 8px;">{{ $member->phone }}</td>
                        </tr>
                        <tr>
                            <td style="padding: 8px;"><strong>Situation matrimoniale</strong></td>
                            <td style="padding: 8px;">{{ $member->marital_status }}</td>
                        </tr>
                        <tr style="background-color: #f9fafb;">
                            <td style="padding: 8px;"><strong>Profil professionnel</strong></td>
                            <td style="padding: 8px;">{{ $member->professional_profile }}</td>
                        </tr>
                        <tr>
                            <td style="padding: 8px;"><strong>Lieu de résidence</strong></td>
                            <td style="padding: 8px;">{{ $member->residence }}</td>
                        </tr>
                        <tr style="background-color: #f9fafb;">
                            <td style="padding: 8px;"><strong>Type de bénévolat</strong></td>
                            <td style="padding: 8px;">{{ $member->benevolent_type->label }}</td>
                        </tr>
                        @if ($member->is_benevolent == 1)
                            <tr>
                                <td style="padding: 8px;"><strong>Expérience en bénévolat</strong></td>
                                <td style="padding: 8px;">{{ $member->benevolent_experience }}</td>
                            </tr>
                        @endif
                        <tr style="background-color: #f9fafb;">
                            <td style="padding: 8px;"><strong>Date d'inscription</strong></td>
                            <td style="padding: 8px;">{{ $member->created_at->format('d/m/Y à H:i') }}</td>
                        </tr>
                    </tbody>
                </table>
            @else

            <p style="margin-bottom: 16px;">Bonjour {{ $member->name }},</p>
            <p>Merci pour votre inscription sur <strong>{{ config('app.name') }}</strong> 🥳</p>
            <p>Votre compte a été validé. Vous pouvez maintenant accéder à nos services.</p>
            <p style="margin-top: 20px;">À très bientôt !</p>
            @endif
        </div>

        <div style="background-color: #f9fafb; text-align: center; padding: 12px; font-size: 12px; color: #6b7280;">
            {{ config('app.name') }} – {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>
</body>

</html>
