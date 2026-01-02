<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Devenez un pro de la compta avec ComptaBE</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f4f4f4;">
    <div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); padding: 30px; text-align: center; border-radius: 12px 12px 0 0;">
        <h1 style="color: white; margin: 0; font-size: 28px;">🎓 Maîtrisez ComptaBE</h1>
        <p style="color: #d1fae5; margin: 10px 0 0 0; font-size: 14px;">Jour 7 - Vous êtes maintenant un pro !</p>
    </div>

    <div style="background: white; padding: 40px; border-radius: 0 0 12px 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <p style="font-size: 16px; margin-bottom: 20px;">Bonjour <strong>{{ $userName }}</strong>,</p>

        <p style="margin-bottom: 20px;">
            Cela fait maintenant une semaine que vous utilisez ComptaBE ! 🎉 Aujourd'hui, découvrons les <strong>fonctionnalités avancées</strong> qui vont faire de vous un véritable expert.
        </p>

        <div style="background: #f0fdf4; border-left: 4px solid #10b981; padding: 20px; margin: 30px 0; border-radius: 4px;">
            <h3 style="margin-top: 0; color: #065f46;">🏆 Votre progression</h3>
            <p style="margin-bottom: 0;">
                Vous avez déjà parcouru un long chemin ! Continuons ensemble pour maîtriser tous les aspects de votre comptabilité.
            </p>
        </div>

        <h2 style="color: #1f2937; font-size: 20px; margin-top: 30px;">Fonctionnalités avancées à découvrir :</h2>

        <div style="margin: 20px 0;">
            <!-- TVA -->
            <div style="background: #fafafa; padding: 20px; border-radius: 8px; margin-bottom: 15px; border-left: 3px solid #10b981;">
                <h3 style="margin: 0 0 10px 0; color: #1f2937; font-size: 16px;">📋 Déclaration TVA automatique</h3>
                <p style="margin: 0 0 10px 0; color: #6b7280; font-size: 14px;">
                    ComptaBE génère automatiquement votre déclaration TVA selon votre régime (mensuel ou trimestriel). Export direct au format Intervat !
                </p>
                <ul style="color: #6b7280; font-size: 13px; margin: 10px 0; padding-left: 20px;">
                    <li>Calcul automatique des grilles</li>
                    <li>Vérification des incohérences</li>
                    <li>Export XML pour Intervat</li>
                    <li>Archivage des déclarations</li>
                </ul>
                <a href="{{ config('app.url') }}/vat"
                   style="color: #10b981; font-weight: bold; font-size: 13px; text-decoration: none;">
                    → Accéder à la TVA
                </a>
            </div>

            <!-- Comptabilité analytique -->
            <div style="background: #fafafa; padding: 20px; border-radius: 8px; margin-bottom: 15px; border-left: 3px solid #3b82f6;">
                <h3 style="margin: 0 0 10px 0; color: #1f2937; font-size: 16px;">📊 Comptabilité analytique</h3>
                <p style="margin: 0 0 10px 0; color: #6b7280; font-size: 14px;">
                    Utilisez les tags analytiques pour segmenter vos revenus et dépenses par projet, client ou département.
                </p>
                <ul style="color: #6b7280; font-size: 13px; margin: 10px 0; padding-left: 20px;">
                    <li>Créez vos propres axes analytiques</li>
                    <li>Rapports de rentabilité par projet</li>
                    <li>Comparaison multi-projets</li>
                </ul>
                <a href="{{ config('app.url') }}/analytics"
                   style="color: #3b82f6; font-weight: bold; font-size: 13px; text-decoration: none;">
                    → Découvrir l'analytique
                </a>
            </div>

            <!-- Rapports avancés -->
            <div style="background: #fafafa; padding: 20px; border-radius: 8px; margin-bottom: 15px; border-left: 3px solid #8b5cf6;">
                <h3 style="margin: 0 0 10px 0; color: #1f2937; font-size: 16px;">📈 Rapports financiers avancés</h3>
                <p style="margin: 0 0 10px 0; color: #6b7280; font-size: 14px;">
                    Tableaux de bord personnalisés, prévisions de trésorerie, et analyses comparatives.
                </p>
                <ul style="color: #6b7280; font-size: 13px; margin: 10px 0; padding-left: 20px;">
                    <li>Tableaux de bord personnalisables</li>
                    <li>Prévision de trésorerie (ML)</li>
                    <li>Balance âgée clients/fournisseurs</li>
                    <li>Compte de résultat prévisionnel</li>
                </ul>
                <a href="{{ config('app.url') }}/reports/advanced"
                   style="color: #8b5cf6; font-weight: bold; font-size: 13px; text-decoration: none;">
                    → Voir les rapports avancés
                </a>
            </div>

            <!-- API & Intégrations -->
            <div style="background: #fafafa; padding: 20px; border-radius: 8px; margin-bottom: 15px; border-left: 3px solid #f59e0b;">
                <h3 style="margin: 0 0 10px 0; color: #1f2937; font-size: 16px;">🔌 API & Intégrations</h3>
                <p style="margin: 0 0 10px 0; color: #6b7280; font-size: 14px;">
                    Connectez ComptaBE à vos autres outils (CRM, e-commerce, etc.) via notre API REST.
                </p>
                <ul style="color: #6b7280; font-size: 13px; margin: 10px 0; padding-left: 20px;">
                    <li>API REST complète avec webhooks</li>
                    <li>Zapier/Make pour automatisations</li>
                    <li>Intégrations e-commerce (Shopify, WooCommerce)</li>
                </ul>
                <a href="{{ config('app.url') }}/settings/api"
                   style="color: #f59e0b; font-weight: bold; font-size: 13px; text-decoration: none;">
                    → Accéder à l'API
                </a>
            </div>
        </div>

        <div style="background: linear-gradient(135deg, #ec4899 0%, #db2777 100%); padding: 25px; border-radius: 8px; margin: 30px 0; text-align: center;">
            <h3 style="color: white; margin: 0 0 10px 0; font-size: 18px;">🎁 Offre spéciale onboarding</h3>
            <p style="color: #fce7f3; margin: 0 0 15px 0; font-size: 14px;">
                Profitez de <strong>20% de réduction</strong> sur votre première année en passant au plan Pro avant la fin du mois !
            </p>
            <a href="{{ config('app.url') }}/pricing"
               style="display: inline-block; background: white; color: #db2777; padding: 12px 30px; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 14px;">
                Découvrir les plans
            </a>
        </div>

        <h3 style="color: #1f2937; margin-top: 30px;">🎯 Vos prochaines étapes :</h3>
        <div style="background: #f9fafb; padding: 20px; border-radius: 8px; margin: 15px 0;">
            <ol style="color: #4b5563; font-size: 14px; margin: 0; padding-left: 20px;">
                <li style="margin-bottom: 10px;">Complétez votre profil entreprise (logo, signature email, coordonnées bancaires)</li>
                <li style="margin-bottom: 10px;">Configurez vos préférences de facturation (numérotation, mentions légales)</li>
                <li style="margin-bottom: 10px;">Invitez votre comptable ou collaborateurs</li>
                <li style="margin-bottom: 10px;">Explorez notre centre d'aide et tutoriels vidéo</li>
                <li>Rejoignez notre communauté sur Discord pour échanger avec d'autres utilisateurs</li>
            </ol>
        </div>

        <div style="background: #fffbeb; border-radius: 8px; padding: 20px; margin-top: 30px; text-align: center;">
            <p style="margin: 0 0 15px 0; color: #78350f; font-size: 15px;">
                <strong>📚 Ressources pour aller plus loin :</strong>
            </p>
            <div style="display: flex; justify-content: space-around; flex-wrap: wrap; gap: 10px;">
                <a href="{{ config('app.url') }}/help" style="color: #f59e0b; font-weight: 500; font-size: 13px; text-decoration: none;">Centre d'aide</a>
                <a href="{{ config('app.url') }}/tutorials" style="color: #f59e0b; font-weight: 500; font-size: 13px; text-decoration: none;">Tutoriels vidéo</a>
                <a href="{{ config('app.url') }}/webinars" style="color: #f59e0b; font-weight: 500; font-size: 13px; text-decoration: none;">Webinars gratuits</a>
                <a href="{{ config('app.url') }}/blog" style="color: #f59e0b; font-weight: 500; font-size: 13px; text-decoration: none;">Blog compta</a>
            </div>
        </div>

        <div style="border-top: 1px solid #e5e7eb; margin-top: 40px; padding-top: 30px; text-align: center;">
            <p style="color: #1f2937; font-size: 16px; margin-bottom: 15px;">
                <strong>Merci de faire confiance à ComptaBE ! 🙏</strong>
            </p>
            <p style="color: #6b7280; font-size: 14px; margin-bottom: 20px;">
                Notre mission est de simplifier votre comptabilité pour que vous puissiez vous concentrer sur votre activité.
            </p>
            <p style="color: #6b7280; font-size: 13px;">
                Une question ? Un feedback ? Répondez simplement à cet email, nous lisons tous vos messages !
            </p>
        </div>

        <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
            <p style="color: #9ca3af; font-size: 12px; margin: 0;">
                Ceci est le dernier email de votre série d'onboarding.<br>
                Vous continuerez à recevoir nos newsletters mensuelles avec nos nouveautés.<br>
                <a href="{{ config('app.url') }}/settings/notifications" style="color: #10b981;">Gérer mes préférences email</a>
            </p>
        </div>
    </div>
</body>
</html>
