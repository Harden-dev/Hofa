<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Nouveau message de contact</title>
</head>
<body style="background-color: #f3f4f6; padding: 20px; font-family: Arial, sans-serif;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); overflow: hidden;">

        <div style="background-color: #049744; padding: 20px;">
            <h2 style="color: #ffffff; font-size: 20px; margin: 0;">📬 Nouveau message de contact</h2>
        </div>

        <div style="padding: 24px; color: #1f2937;">
            <p><strong>👤 Nom :</strong> {{ $name }}</p>
            <p><strong>📧 Adresse e-mail :</strong> {{ $email }}</p>

            @if(!empty($phone))
                <p><strong>📞 Téléphone :</strong> {{ $phone }}</p>
            @endif

            @if(!empty($subject))
                <p><strong>📝 Sujet :</strong> {{ $subject }}</p>
            @endif

            <hr style="margin: 24px 0; border: none; border-top: 1px solid #e5e7eb;">

            <p style="font-weight: bold; margin-bottom: 8px;">💬 Message :</p>
            <p style="white-space: pre-line;">{{ $messageContent }}</p>
        </div>

        <div style="background-color: #f9fafb; text-align: center; padding: 12px; font-size: 12px; color: #6b7280;">
            Reçu depuis le site {{ config('app.name') }} – {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>
</body>
</html>
