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
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Bienvenue sur la plateforme de HOFA</h1>
        </div>
    </div>
    <div class="content">
        <p>Vous avez été ajouté à la plateforme de HOFA</p>
        <p>Votre mot de passe est : {{ $user->password }}</p>
        <p>Veuillez utiliser ce mot de passe pour vous connecter à la plateforme</p>
        <p>Merci de votre inscription</p>
        <p>L'équipe de HOFA</p>
    </div>
    <div class="footer">
        <p>L'équipe de HOFA</p>
    </div>
</body>

</html>
