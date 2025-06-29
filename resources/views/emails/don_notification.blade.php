<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Notification de don</title>
</head>
<body style="background-color: #f3f4f6; padding: 20px; font-family: Arial, sans-serif;">
    <div style="max-width: 600px; margin: auto; background-color: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
        <div style="background-color: #10b981; color: white; padding: 20px;">
            <h2 style="margin: 0;">
                {{ $isAdmin ? '🎉 Nouveau don reçu' : '🙏 Merci pour votre soutien !' }}
            </h2>
        </div>

        <div style="padding: 20px; color: #111827;">
            @if($isAdmin)
                <p><strong>Nom du donateur :</strong> {{ $enfiler->name }}</p>
                <p><strong>Email :</strong> {{ $enfiler->email }}</p>
            @else
                <p>Bonjour {{ $enfiler->name }},</p>
                <p>Nous vous remercions sincèrement pour votre don. Votre soutien est précieux pour nous. 🙏</p>
            @endif

            
            @if($isAdmin)
                <p><strong>Type de don :</strong> {{ $enfiler->type_enfiler->label }}</p>
            @else
                <p><strong>Motivation :</strong> {{ $enfiler->motivation }}</p>
            @endif
            <p><strong>Date :</strong> {{ $enfiler->created_at->format('d/m/Y à H:i') }}</p>

            @if($enfiler->message)
                <p><strong>Message du donateur :</strong> <br>{{ $enfiler->message }}</p>
            @endif
        </div>

        <div style="background-color: #f9fafb; text-align: center; padding: 12px; font-size: 12px; color: #6b7280;">
            {{ config('app.name') }} – Merci pour votre confiance 💚
        </div>
    </div>
</body>
</html>
