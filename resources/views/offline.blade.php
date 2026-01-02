<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hors ligne - ComptaBE</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .offline-container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            max-width: 500px;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .offline-icon {
            width: 120px;
            height: 120px;
            margin: 0 auto 30px;
            background: #f3f4f6;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-center;
        }

        .offline-icon svg {
            width: 60px;
            height: 60px;
            color: #9ca3af;
        }

        h1 {
            font-size: 28px;
            color: #1f2937;
            margin-bottom: 15px;
        }

        p {
            color: #6b7280;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .retry-button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 15px 40px;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .retry-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
        }

        .retry-button:active {
            transform: translateY(0);
        }

        .connection-status {
            margin-top: 20px;
            padding: 12px;
            background: #fef3c7;
            border-radius: 8px;
            font-size: 14px;
            color: #92400e;
            display: none;
        }

        .connection-status.online {
            background: #d1fae5;
            color: #065f46;
            display: block;
        }

        .features-list {
            text-align: left;
            margin-top: 30px;
            padding-top: 30px;
            border-top: 1px solid #e5e7eb;
        }

        .features-list h2 {
            font-size: 18px;
            color: #1f2937;
            margin-bottom: 15px;
        }

        .features-list ul {
            list-style: none;
        }

        .features-list li {
            color: #6b7280;
            font-size: 14px;
            padding: 8px 0;
            padding-left: 30px;
            position: relative;
        }

        .features-list li:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #10b981;
            font-weight: bold;
            font-size: 18px;
        }

        @media (prefers-color-scheme: dark) {
            body {
                background: linear-gradient(135deg, #1e3a8a 0%, #581c87 100%);
            }

            .offline-container {
                background: #1f2937;
            }

            .offline-icon {
                background: #374151;
            }

            h1 {
                color: #f9fafb;
            }

            p {
                color: #d1d5db;
            }

            .features-list h2 {
                color: #f9fafb;
            }

            .features-list li {
                color: #d1d5db;
            }

            .features-list {
                border-top-color: #374151;
            }
        }
    </style>
</head>
<body>
    <div class="offline-container">
        <div class="offline-icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636a9 9 0 010 12.728m0 0l-2.829-2.829m2.829 2.829L21 21M15.536 8.464a5 5 0 010 7.072m0 0l-2.829-2.829m-4.243 2.829a4.978 4.978 0 01-1.414-2.83m-1.414 5.658a9 9 0 01-2.167-9.238m7.824 2.167a1 1 0 111.414 1.414m-1.414-1.414L3 3m8.293 8.293l1.414 1.414" />
            </svg>
        </div>

        <h1>Vous êtes hors ligne</h1>
        <p>
            Il semblerait que vous n'ayez pas de connexion Internet pour le moment.
            Certaines fonctionnalités peuvent être limitées.
        </p>

        <button class="retry-button" onclick="window.location.reload()">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 20px; height: 20px;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            Réessayer
        </button>

        <div class="connection-status" id="connectionStatus">
            ✓ Connexion rétablie ! Vous pouvez maintenant rafraîchir la page.
        </div>

        <div class="features-list">
            <h2>Fonctionnalités disponibles hors ligne :</h2>
            <ul>
                <li>Consultation de vos données récemment consultées</li>
                <li>Navigation dans l'application</li>
                <li>Accès aux factures mises en cache</li>
            </ul>
        </div>
    </div>

    <script>
        // Surveiller le retour de la connexion
        window.addEventListener('online', () => {
            const status = document.getElementById('connectionStatus');
            status.classList.add('online');
            status.style.display = 'block';
        });

        window.addEventListener('offline', () => {
            const status = document.getElementById('connectionStatus');
            status.classList.remove('online');
            status.style.display = 'none';
        });

        // Vérifier la connexion au chargement
        if (navigator.onLine) {
            document.getElementById('connectionStatus').classList.add('online');
            document.getElementById('connectionStatus').style.display = 'block';
        }
    </script>
</body>
</html>
