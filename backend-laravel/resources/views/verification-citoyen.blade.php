<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Confirmation de votre adresse e-mail</title>
</head>
<body style="font-family: Arial, sans-serif; background:#f4f4f4; padding:20px;">
    <div style="max-width:560px; margin:auto; background:#fff; padding:30px; border-radius:8px;">
        <h2 style="color:#1e3a8a;">Bonjour {{ $citoyen->prenom }} {{ $citoyen->nom }},</h2>

        <p>Merci de vous être inscrit sur la plateforme d'état civil.</p>
        <p>Pour activer votre compte, confirmez votre adresse e-mail :</p>

        <p style="text-align:center; margin:30px 0;">
            <a href="{{ $lienVerification }}"
               style="background:#1e3a8a; color:#fff; padding:12px 24px; text-decoration:none; border-radius:6px;">
                Confirmer mon adresse e-mail
            </a>
        </p>

        <p style="font-size:13px; color:#666;">
            Si le bouton ne fonctionne pas, copiez ce lien dans votre navigateur :<br>
            {{ $lienVerification }}
        </p>

        <p style="font-size:13px; color:#666;">
            Si vous n'êtes pas à l'origine de cette inscription, ignorez cet email.
        </p>
    </div>
</body>
</html>