<x-mail::message>
# Bienvenue sur ComptaBE !

Bonjour,

**{{ $inviterName }}** vous invite à rejoindre **{{ $companyName }}** sur ComptaBE en tant que **{{ $role }}**.

ComptaBE est une plateforme de comptabilité belge moderne qui vous permet de gérer facilement vos factures, déclarations TVA, et bien plus encore.

<x-mail::button :url="$acceptUrl" color="primary">
Accepter l'invitation
</x-mail::button>

## Que se passe-t-il ensuite ?

En cliquant sur le bouton ci-dessus, vous pourrez :
- Créer votre mot de passe
- Accéder immédiatement à votre espace
- Commencer à utiliser ComptaBE

<x-mail::panel>
**Important** : Cette invitation expire le **{{ $expiresAt }}**.

Si vous n'acceptez pas l'invitation avant cette date, elle ne sera plus valide et vous devrez demander une nouvelle invitation.
</x-mail::panel>

## Besoin d'aide ?

Si vous rencontrez des difficultés ou avez des questions, n'hésitez pas à nous contacter à [support@comptabe.be](mailto:support@comptabe.be).

Vous pouvez également copier/coller ce lien dans votre navigateur :
{{ $acceptUrl }}

Cordialement,<br>
L'équipe ComptaBE

---

<small>
Si vous n'attendiez pas cette invitation, vous pouvez ignorer cet email en toute sécurité.
Aucun compte ne sera créé sans votre confirmation.
</small>
</x-mail::message>
