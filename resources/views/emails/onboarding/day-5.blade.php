<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Automatisez votre gestion avec ComptaBE</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f4f4f4;">
    <div style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); padding: 30px; text-align: center; border-radius: 12px 12px 0 0;">
        <h1 style="color: white; margin: 0; font-size: 28px;">🚀 Automatisez votre gestion</h1>
        <p style="color: #ede9fe; margin: 10px 0 0 0; font-size: 14px;">Jour 5 de votre parcours ComptaBE</p>
    </div>

    <div style="background: white; padding: 40px; border-radius: 0 0 12px 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <p style="font-size: 16px; margin-bottom: 20px;">Bonjour <strong>{{ $userName }}</strong>,</p>

        <p style="margin-bottom: 20px;">
            Félicitations ! Vous êtes déjà bien lancé avec ComptaBE. Aujourd'hui, découvrons comment <strong>automatiser</strong> vos tâches répétitives pour gagner un temps précieux.
        </p>

        <div style="background: #fef3c7; border-left: 4px solid #f59e0b; padding: 20px; margin: 30px 0; border-radius: 4px;">
            <h3 style="margin-top: 0; color: #92400e;">⏱️ Le saviez-vous ?</h3>
            <p style="margin-bottom: 0;">
                Les utilisateurs de ComptaBE économisent en moyenne <strong>5 heures par mois</strong> grâce à l'automatisation !
            </p>
        </div>

        <h2 style="color: #1f2937; font-size: 20px; margin-top: 30px;">3 automatisations essentielles :</h2>

        <div style="margin: 20px 0;">
            <div style="background: #f9fafb; padding: 20px; border-radius: 8px; margin-bottom: 15px; border: 1px solid #e5e7eb;">
                <div style="display: flex; align-items: start;">
                    <div style="font-size: 32px; margin-right: 15px;">🔄</div>
                    <div>
                        <h3 style="margin: 0 0 10px 0; color: #1f2937; font-size: 16px;">Factures récurrentes</h3>
                        <p style="margin: 0 0 10px 0; color: #6b7280; font-size: 14px;">
                            Pour vos abonnements et prestations mensuelles, créez une facture récurrente. Elle sera générée et envoyée automatiquement chaque mois !
                        </p>
                        <a href="{{ config('app.url') }}/recurring-invoices/create"
                           style="color: #8b5cf6; font-weight: bold; font-size: 13px; text-decoration: none;">
                            → Créer une facture récurrente
                        </a>
                    </div>
                </div>
            </div>

            <div style="background: #f9fafb; padding: 20px; border-radius: 8px; margin-bottom: 15px; border: 1px solid #e5e7eb;">
                <div style="display: flex; align-items: start;">
                    <div style="font-size: 32px; margin-right: 15px;">🏦</div>
                    <div>
                        <h3 style="margin: 0 0 10px 0; color: #1f2937; font-size: 16px;">Connexion bancaire (Open Banking)</h3>
                        <p style="margin: 0 0 10px 0; color: #6b7280; font-size: 14px;">
                            Synchronisez votre compte bancaire. Vos transactions seront importées automatiquement et le rapprochement sera un jeu d'enfant !
                        </p>
                        <a href="{{ config('app.url') }}/bank/connect"
                           style="color: #8b5cf6; font-weight: bold; font-size: 13px; text-decoration: none;">
                            → Connecter ma banque
                        </a>
                    </div>
                </div>
            </div>

            <div style="background: #f9fafb; padding: 20px; border-radius: 8px; margin-bottom: 15px; border: 1px solid #e5e7eb;">
                <div style="display: flex; align-items: start;">
                    <div style="font-size: 32px; margin-right: 15px;">📧</div>
                    <div>
                        <h3 style="margin: 0 0 10px 0; color: #1f2937; font-size: 16px;">Rappels automatiques</h3>
                        <p style="margin: 0 0 10px 0; color: #6b7280; font-size: 14px;">
                            Activez les rappels automatiques pour les factures impayées. ComptaBE relance vos clients à J+7, J+15 et J+30 après l'échéance.
                        </p>
                        <a href="{{ config('app.url') }}/settings/reminders"
                           style="color: #8b5cf6; font-weight: bold; font-size: 13px; text-decoration: none;">
                            → Activer les rappels
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); padding: 25px; border-radius: 8px; margin: 30px 0;">
            <h3 style="color: white; margin: 0 0 15px 0; font-size: 18px;">🤖 Nouveau : Assistant IA</h3>
            <p style="color: #dbeafe; margin: 0 0 15px 0; font-size: 14px;">
                Demandez à l'assistant IA de créer vos factures, analyser vos données ou répondre à vos questions en langage naturel !
            </p>
            <p style="color: #dbeafe; margin: 0; font-size: 13px; font-style: italic;">
                💬 "Crée une facture de 1500€ HT pour le client XYZ"<br>
                💬 "Quel est mon chiffre d'affaires du mois dernier ?"<br>
                💬 "Liste mes factures impayées"
            </p>
        </div>

        <h3 style="color: #1f2937; margin-top: 30px;">📊 Rapports automatiques :</h3>
        <p style="color: #6b7280;">
            Configurez vos rapports mensuels (TVA, balance, compte de résultat) pour les recevoir automatiquement par email chaque début de mois.
        </p>
        <a href="{{ config('app.url') }}/reports/automated"
           style="display: inline-block; background: #8b5cf6; color: white; padding: 10px 24px; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 14px; margin-top: 10px;">
            Configurer mes rapports
        </a>

        <div style="background: #ecfdf5; border-radius: 8px; padding: 20px; margin-top: 30px;">
            <p style="margin: 0; color: #065f46; font-size: 14px;">
                <strong>✨ Pro tip :</strong> Plus vous automatisez, plus vous gagnez du temps pour développer votre activité !
            </p>
        </div>

        <div style="border-top: 1px solid #e5e7eb; margin-top: 40px; padding-top: 30px; color: #6b7280; font-size: 13px;">
            <p style="margin-bottom: 10px;"><strong>Questions ou difficultés ?</strong></p>
            <p style="margin: 5px 0;">
                Notre équipe support est là pour vous aider :<br>
                • 💬 Chat en direct dans l'application<br>
                • 📹 Tutoriels vidéo : <a href="{{ config('app.url') }}/tutorials" style="color: #8b5cf6;">tutoriels.comptabe.be</a><br>
                • ✉️ Email : support@comptabe.be
            </p>
        </div>

        <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
            <p style="color: #9ca3af; font-size: 12px; margin: 0;">
                Vous recevez cet email dans le cadre de votre onboarding ComptaBE.<br>
                <a href="{{ config('app.url') }}/settings/notifications" style="color: #8b5cf6;">Gérer mes préférences email</a>
            </p>
        </div>
    </div>
</body>
</html>
