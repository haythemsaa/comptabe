<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vos premières factures avec ComptaBE</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f4f4f4;">
    <div style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); padding: 30px; text-align: center; border-radius: 12px 12px 0 0;">
        <h1 style="color: white; margin: 0; font-size: 28px;">📄 Vos premières factures</h1>
        <p style="color: #e0e7ff; margin: 10px 0 0 0; font-size: 14px;">Jour 2 de votre parcours ComptaBE</p>
    </div>

    <div style="background: white; padding: 40px; border-radius: 0 0 12px 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <p style="font-size: 16px; margin-bottom: 20px;">Bonjour <strong>{{ $userName }}</strong>,</p>

        <p style="margin-bottom: 20px;">
            Bienvenue dans ComptaBE ! Aujourd'hui, nous allons vous montrer comment créer votre première facture en quelques clics.
        </p>

        <div style="background: #f0f9ff; border-left: 4px solid #3b82f6; padding: 20px; margin: 30px 0; border-radius: 4px;">
            <h3 style="margin-top: 0; color: #1e40af;">💡 Astuce du jour</h3>
            <p style="margin-bottom: 0;">
                Saviez-vous que vous pouvez créer une facture en 3 étapes simples ?
            </p>
        </div>

        <h2 style="color: #1f2937; font-size: 20px; margin-top: 30px;">Comment créer votre première facture :</h2>

        <div style="margin: 20px 0;">
            <div style="display: flex; align-items: start; margin-bottom: 20px;">
                <div style="background: #3b82f6; color: white; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; margin-right: 15px; flex-shrink: 0;">1</div>
                <div>
                    <strong style="color: #1f2937;">Créez un client</strong>
                    <p style="margin: 5px 0 0 0; color: #6b7280;">Allez dans "Clients & Fournisseurs" et ajoutez votre premier client avec ses informations (nom, TVA, adresse).</p>
                </div>
            </div>

            <div style="display: flex; align-items: start; margin-bottom: 20px;">
                <div style="background: #3b82f6; color: white; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; margin-right: 15px; flex-shrink: 0;">2</div>
                <div>
                    <strong style="color: #1f2937;">Ajoutez vos produits/services</strong>
                    <p style="margin: 5px 0 0 0; color: #6b7280;">Dans "Produits", créez vos articles avec prix et taux de TVA. Ils seront réutilisables !</p>
                </div>
            </div>

            <div style="display: flex; align-items: start; margin-bottom: 20px;">
                <div style="background: #3b82f6; color: white; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; margin-right: 15px; flex-shrink: 0;">3</div>
                <div>
                    <strong style="color: #1f2937;">Créez votre facture</strong>
                    <p style="margin: 5px 0 0 0; color: #6b7280;">Allez dans "Factures" → "Nouvelle facture", sélectionnez votre client, ajoutez vos lignes et validez !</p>
                </div>
            </div>
        </div>

        <div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); padding: 20px; border-radius: 8px; text-align: center; margin: 30px 0;">
            <p style="color: white; margin: 0 0 15px 0; font-size: 16px;">Prêt à créer votre première facture ?</p>
            <a href="{{ config('app.url') }}/invoices/create"
               style="display: inline-block; background: white; color: #059669; padding: 12px 30px; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 14px;">
                Créer ma facture
            </a>
        </div>

        <h3 style="color: #1f2937; margin-top: 30px;">🚀 Fonctionnalités bonus :</h3>
        <ul style="color: #6b7280; line-height: 1.8;">
            <li><strong>Envoi automatique :</strong> Envoyez vos factures par email directement depuis ComptaBE</li>
            <li><strong>Peppol :</strong> Factures électroniques conformes pour vos clients publics</li>
            <li><strong>Rappels :</strong> Relances automatiques pour les factures impayées</li>
            <li><strong>PDF personnalisé :</strong> Votre logo et vos couleurs sur chaque facture</li>
        </ul>

        <div style="border-top: 1px solid #e5e7eb; margin-top: 40px; padding-top: 30px; color: #6b7280; font-size: 13px;">
            <p style="margin-bottom: 10px;"><strong>Besoin d'aide ?</strong></p>
            <p style="margin: 5px 0;">
                • 💬 Utilisez l'assistant IA dans l'application<br>
                • 📚 Consultez notre centre d'aide : <a href="{{ config('app.url') }}/help" style="color: #3b82f6;">aide.comptabe.be</a><br>
                • ✉️ Contactez-nous : support@comptabe.be
            </p>
        </div>

        <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
            <p style="color: #9ca3af; font-size: 12px; margin: 0;">
                Vous recevez cet email car vous venez de vous inscrire sur ComptaBE.<br>
                <a href="{{ config('app.url') }}/settings/notifications" style="color: #3b82f6;">Gérer mes préférences email</a>
            </p>
        </div>
    </div>
</body>
</html>
