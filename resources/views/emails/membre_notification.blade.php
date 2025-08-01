<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Demande d'inscription reçue</title>
</head>

<body style="background-color: #f3f4f6; font-family: 'Montserrat', Arial, sans-serif; padding: 20px;">
    <div
        style="max-width: 600px; margin: auto; background-color: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.05); font-family: 'Montserrat', Arial, sans-serif;">

        <div style="background-color: #10b981; color: white; padding: 20px;">
            <h2 style="margin: 0;">
                @if ($isAdmin)
                    👤 Nouvelle demande d'inscription
                @else
                    📝 Demande d'inscription reçue
                @endif
            </h2>
        </div>

        <div style="padding: 24px; color: #111827; font-size: 14px;">
            @if ($isAdmin)
                <p style="margin-bottom: 16px;">Une nouvelle demande d'inscription a été soumise :</p>

                <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                    <tbody>
                        <tr style="background-color: #f9fafb;">
                            <td style="padding: 8px;"><strong>Type</strong></td>
                            <td style="padding: 8px;">{{ $member->type === 'individual' ? 'Individuel' : 'Entreprise' }}</td>
                        </tr>
                        <tr>
                            <td style="padding: 8px;"><strong>Nom</strong></td>
                            <td style="padding: 8px;">{{ $member->full_name }}</td>
                        </tr>
                        @if($member->type === 'company' && $member->bossName)
                        <tr style="background-color: #f9fafb;">
                            <td style="padding: 8px;"><strong>Responsable</strong></td>
                            <td style="padding: 8px;">{{ $member->bossName }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td style="padding: 8px;"><strong>Email</strong></td>
                            <td style="padding: 8px;">{{ $member->email }}</td>
                        </tr>
                        <tr style="background-color: #f9fafb;">
                            <td style="padding: 8px;"><strong>Téléphone</strong></td>
                            <td style="padding: 8px;">{{ $member->phone }}</td>
                        </tr>
                        @if($member->gender)
                        <tr>
                            <td style="padding: 8px;"><strong>Genre</strong></td>
                            <td style="padding: 8px;">{{ $member->gender }}</td>
                        </tr>
                        @endif
                        @if($member->nationality)
                        <tr style="background-color: #f9fafb;">
                            <td style="padding: 8px;"><strong>Nationalité</strong></td>
                            <td style="padding: 8px;">{{ $member->nationality }}</td>
                        </tr>
                        @endif
                        @if($member->matrimonial)
                        <tr>
                            <td style="padding: 8px;"><strong>Situation matrimoniale</strong></td>
                            <td style="padding: 8px;">{{ $member->matrimonial }}</td>
                        </tr>
                        @endif
                        @if($member->habit)
                        <tr style="background-color: #f9fafb;">
                            <td style="padding: 8px;"><strong>Lieu de résidence</strong></td>
                            <td style="padding: 8px;">{{ $member->habit }}</td>
                        </tr>
                        @endif
                        @if($member->job)
                        <tr>
                            <td style="padding: 8px;"><strong>Profession</strong></td>
                            <td style="padding: 8px;">{{ $member->job }}</td>
                        </tr>
                        @endif
                        @if($member->origin)
                        <tr style="background-color: #f9fafb;">
                            <td style="padding: 8px;"><strong>Origine</strong></td>
                            <td style="padding: 8px;">{{ $member->origin }}</td>
                        </tr>
                        @endif
                        @if($member->web)
                        <tr>
                            <td style="padding: 8px;"><strong>Site web</strong></td>
                            <td style="padding: 8px;">{{ $member->web }}</td>
                        </tr>
                        @endif
                        @if($member->activity)
                        <tr style="background-color: #f9fafb;">
                            <td style="padding: 8px;"><strong>Activités</strong></td>
                            <td style="padding: 8px;">{{ $member->activity }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td style="padding: 8px;"><strong>Bénévole</strong></td>
                            <td style="padding: 8px;">{{ $member->is_volunteer ? 'Oui' : 'Non' }}</td>
                        </tr>
                        @if($member->is_volunteer && $member->volunteer)
                        <tr style="background-color: #f9fafb;">
                            <td style="padding: 8px;"><strong>Description du bénévolat</strong></td>
                            <td style="padding: 8px;">{{ $member->volunteer }}</td>
                        </tr>
                        @endif
                        @if($member->bio)
                        <tr>
                            <td style="padding: 8px;"><strong>Biographie</strong></td>
                            <td style="padding: 8px;">{{ $member->bio }}</td>
                        </tr>
                        @endif
                        <tr style="background-color: #f9fafb;">
                            <td style="padding: 8px;"><strong>Date de demande</strong></td>
                            <td style="padding: 8px;">{{ $member->created_at->format('d/m/Y à H:i') }}</td>
                        </tr>
                    </tbody>
                </table>

                <div style="margin-top: 20px; padding: 16px; background-color: #fef3c7; border-left: 4px solid #f59e0b; border-radius: 4px;">
                    <p style="margin: 0; color: #92400e; font-weight: 500;">
                        ⚠️ <strong>Action requise :</strong> Cette demande nécessite votre approbation.
                    </p>
                    <p style="margin: 8px 0 0 0; color: #92400e; font-size: 13px;">
                        Connectez-vous à votre espace d'administration pour approuver ou rejeter cette demande.
                    </p>
                </div>
            @else
                <p style="margin-bottom: 16px;">Bonjour {{ $member->full_name }},</p>

                <p>Nous avons bien reçu votre demande d'inscription sur <strong>{{ config('app.name') }}</strong> 📝</p>

                <div style="background-color: #f0f9ff; border-left: 4px solid #0ea5e9; padding: 16px; margin: 20px 0; border-radius: 4px;">
                    <p style="margin: 0; color: #0c4a6e; font-weight: 500;">
                        🔄 <strong>Votre demande est en cours d'examen</strong>
                    </p>
                    <p style="margin: 8px 0 0 0; color: #0c4a6e; font-size: 13px;">
                        Notre équipe va examiner votre demande et vous informera de la décision par email dans les plus brefs délais.
                    </p>
                </div>

                <p>Voici un récapitulatif de votre demande :</p>
                <ul style="margin: 16px 0; padding-left: 20px;">
                    <li><strong>Type :</strong> {{ $member->type === 'individual' ? 'Individuel' : 'Entreprise' }}</li>
                    <li><strong>Email :</strong> {{ $member->email }}</li>
                    <li><strong>Téléphone :</strong> {{ $member->phone }}</li>
                    @if($member->is_volunteer)
                    <li><strong>Statut bénévole :</strong> Oui</li>
                    @endif
                </ul>

                <p style="margin-top: 20px;">Nous vous remercions pour votre intérêt et votre patience !</p>

                <p>À très bientôt,<br>
                <strong>L'équipe {{ config('app.name') }}</strong></p>
            @endif
        </div>

        <div style="background-color: #f9fafb; text-align: center; padding: 12px; font-size: 12px; color: #6b7280;">
            {{ config('app.name') }} – {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>
</body>

</html>
