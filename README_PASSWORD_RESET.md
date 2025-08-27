# Système de Reset de Mot de Passe - HOFA

## Fonctionnalités

Le système de reset de mot de passe permet aux utilisateurs de réinitialiser leur mot de passe en recevant un mot de passe temporaire par email.

## Processus

### 1. Demande de Reset
L'utilisateur fait une demande de reset en fournissant son email.

**Endpoint :** `POST /api/v1/reset-password`

**Body :**
```json
{
    "email": "user@example.com"
}
```

**Réponse :**
```json
{
    "message": "Un nouveau mot de passe temporaire a été envoyé à votre adresse e-mail"
}
```

### 2. Email de Reset
L'utilisateur reçoit un email contenant :
- Son email
- Un mot de passe temporaire (8 caractères aléatoires)
- Des instructions de sécurité

### 3. Connexion avec Mot de Passe Temporaire
L'utilisateur se connecte avec son email et le mot de passe temporaire.

**Endpoint :** `POST /api/v1/login`

**Body :**
```json
{
    "email": "user@example.com",
    "password": "motDePasseTemporaire"
}
```

**Réponse (si mot de passe temporaire) :**
```json
{
    "error": "Vous devez changer votre mot de passe temporaire. Veuillez vous reconnecter après avoir changé votre mot de passe.",
    "requires_password_change": true
}
```

### 4. Changement de Mot de Passe
L'utilisateur doit changer son mot de passe temporaire.

**Endpoint :** `POST /api/v1/change-password`

**Headers :** `Authorization: Bearer {token}`

**Body :**
```json
{
    "current_password": "motDePasseTemporaire",
    "new_password": "nouveauMotDePasse",
    "new_password_confirmation": "nouveauMotDePasse"
}
```

**Réponse :**
```json
{
    "message": "Le mot de passe a été modifié avec succès"
}
```

## Sécurité

- Le mot de passe temporaire est généré aléatoirement (8 caractères)
- L'utilisateur ne peut pas utiliser l'application sans changer le mot de passe temporaire
- Le mot de passe temporaire est valide pour une seule connexion
- Tous les changements sont loggés

## Fichiers Créés/Modifiés

### Nouveaux Fichiers
- `app/Mail/PasswordResetMail.php` - Classe Mail pour l'envoi du mot de passe temporaire
- `resources/views/emails/password_reset.blade.php` - Template email

### Fichiers Modifiés
- `app/Http/Controllers/API/Auth/AuthController.php` - Ajout de la logique de reset et vérification du mot de passe temporaire

## Configuration Email

Assurez-vous que votre configuration email est correcte dans le fichier `.env` :

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=votre-email@gmail.com
MAIL_PASSWORD=votre-mot-de-passe-app
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=votre-email@gmail.com
MAIL_FROM_NAME="HOFA"
```

## Test

Pour tester le système :

1. Faites une demande de reset : `POST /api/v1/reset-password`
2. Vérifiez l'email reçu
3. Connectez-vous avec le mot de passe temporaire
4. Changez le mot de passe
5. Vérifiez que vous pouvez vous connecter normalement
