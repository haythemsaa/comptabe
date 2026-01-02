<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Déclaration TVA - {{ $declaration->period_start->format('m/Y') }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.4;
            color: #333;
        }

        .header {
            margin-bottom: 30px;
            border-bottom: 2px solid #0066cc;
            padding-bottom: 15px;
        }

        .header h1 {
            color: #0066cc;
            font-size: 24pt;
            margin-bottom: 5px;
        }

        .header .subtitle {
            color: #666;
            font-size: 12pt;
        }

        .company-info {
            margin-bottom: 25px;
            background: #f8f9fa;
            padding: 15px;
            border-left: 4px solid #0066cc;
        }

        .company-info h2 {
            font-size: 14pt;
            margin-bottom: 10px;
            color: #0066cc;
        }

        .company-info p {
            margin: 3px 0;
        }

        .declaration-info {
            margin-bottom: 25px;
            display: table;
            width: 100%;
        }

        .declaration-info .row {
            display: table-row;
        }

        .declaration-info .label {
            display: table-cell;
            font-weight: bold;
            width: 40%;
            padding: 5px 10px;
            background: #f0f0f0;
        }

        .declaration-info .value {
            display: table-cell;
            padding: 5px 10px;
            border-bottom: 1px solid #ddd;
        }

        .amounts-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .amounts-table thead {
            background: #0066cc;
            color: white;
        }

        .amounts-table th,
        .amounts-table td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }

        .amounts-table th {
            font-weight: bold;
        }

        .amounts-table tbody tr:nth-child(even) {
            background: #f8f9fa;
        }

        .amounts-table .amount {
            text-align: right;
            font-family: 'Courier New', monospace;
        }

        .amounts-table .grid-code {
            font-weight: bold;
            color: #0066cc;
            width: 80px;
        }

        .summary {
            margin-top: 30px;
            padding: 15px;
            background: #fff3cd;
            border: 2px solid #ffc107;
            border-radius: 5px;
        }

        .summary h3 {
            color: #856404;
            margin-bottom: 10px;
        }

        .summary .total-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 13pt;
        }

        .summary .total-row.main {
            font-weight: bold;
            font-size: 16pt;
            border-top: 2px solid #856404;
            padding-top: 10px;
            margin-top: 10px;
        }

        .footer {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            font-size: 9pt;
            color: #666;
            text-align: center;
        }

        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 72pt;
            color: rgba(255, 0, 0, 0.1);
            font-weight: bold;
            z-index: -1;
        }

        @page {
            margin: 2cm;
        }
    </style>
</head>
<body>
    @if($declaration->status === 'draft')
    <div class="watermark">BROUILLON</div>
    @endif

    <div class="header">
        <h1>Déclaration TVA</h1>
        <p class="subtitle">Période: {{ $declaration->period_start->format('d/m/Y') }} - {{ $declaration->period_end->format('d/m/Y') }}</p>
    </div>

    <div class="company-info">
        <h2>Informations Entreprise</h2>
        <p><strong>Raison sociale:</strong> {{ $company->name }}</p>
        <p><strong>Numéro TVA:</strong> {{ $company->vat_number }}</p>
        <p><strong>Adresse:</strong> {{ $company->address }}, {{ $company->postal_code }} {{ $company->city }}</p>
        @if($company->email)
        <p><strong>Email:</strong> {{ $company->email }}</p>
        @endif
    </div>

    <div class="declaration-info">
        <div class="row">
            <div class="label">Type de déclaration</div>
            <div class="value">{{ $declaration->declaration_type === 'monthly' ? 'Mensuelle' : 'Trimestrielle' }}</div>
        </div>
        <div class="row">
            <div class="label">Période</div>
            <div class="value">{{ $declaration->period_start->format('F Y') }}</div>
        </div>
        <div class="row">
            <div class="label">Date génération</div>
            <div class="value">{{ now()->format('d/m/Y H:i') }}</div>
        </div>
        <div class="row">
            <div class="label">Statut</div>
            <div class="value">{{ ucfirst($declaration->status) }}</div>
        </div>
    </div>

    <h3 style="margin-top: 20px; color: #0066cc;">Grilles TVA</h3>

    <table class="amounts-table">
        <thead>
            <tr>
                <th>Grille</th>
                <th>Description</th>
                <th class="amount">Montant (€)</th>
            </tr>
        </thead>
        <tbody>
            <!-- Operations sortantes -->
            <tr>
                <td class="grid-code">00</td>
                <td>Opérations à la sortie soumises à un régime particulier</td>
                <td class="amount">{{ number_format($declaration->grid_00 ?? 0, 2, ',', ' ') }}</td>
            </tr>
            <tr>
                <td class="grid-code">01</td>
                <td>Livraisons (base imposable)</td>
                <td class="amount">{{ number_format($declaration->grid_01 ?? 0, 2, ',', ' ') }}</td>
            </tr>
            <tr>
                <td class="grid-code">02</td>
                <td>Prestations de services (base imposable)</td>
                <td class="amount">{{ number_format($declaration->grid_02 ?? 0, 2, ',', ' ') }}</td>
            </tr>
            <tr>
                <td class="grid-code">03</td>
                <td>Opérations à la sortie par lesquelles la taxe est due par le cocontractant</td>
                <td class="amount">{{ number_format($declaration->grid_03 ?? 0, 2, ',', ' ') }}</td>
            </tr>

            <!-- TVA due -->
            <tr style="background: #e7f3ff;">
                <td class="grid-code">54</td>
                <td><strong>TVA due (taux 21%)</strong></td>
                <td class="amount"><strong>{{ number_format($declaration->grid_54 ?? 0, 2, ',', ' ') }}</strong></td>
            </tr>
            <tr style="background: #e7f3ff;">
                <td class="grid-code">55</td>
                <td><strong>TVA due (taux 12%)</strong></td>
                <td class="amount"><strong>{{ number_format($declaration->grid_55 ?? 0, 2, ',', ' ') }}</strong></td>
            </tr>
            <tr style="background: #e7f3ff;">
                <td class="grid-code">56</td>
                <td><strong>TVA due (taux 6%)</strong></td>
                <td class="amount"><strong>{{ number_format($declaration->grid_56 ?? 0, 2, ',', ' ') }}</strong></td>
            </tr>

            <!-- Operations entrantes -->
            <tr>
                <td class="grid-code">81</td>
                <td>Marchandises, matières premières et matières auxiliaires</td>
                <td class="amount">{{ number_format($declaration->grid_81 ?? 0, 2, ',', ' ') }}</td>
            </tr>
            <tr>
                <td class="grid-code">82</td>
                <td>Services et biens divers</td>
                <td class="amount">{{ number_format($declaration->grid_82 ?? 0, 2, ',', ' ') }}</td>
            </tr>
            <tr>
                <td class="grid-code">83</td>
                <td>Biens d'investissement</td>
                <td class="amount">{{ number_format($declaration->grid_83 ?? 0, 2, ',', ' ') }}</td>
            </tr>

            <!-- TVA deductible -->
            <tr style="background: #e7f3ff;">
                <td class="grid-code">59</td>
                <td><strong>TVA déductible</strong></td>
                <td class="amount"><strong>{{ number_format($declaration->grid_59 ?? 0, 2, ',', ' ') }}</strong></td>
            </tr>
        </tbody>
    </table>

    <div class="summary">
        <h3>Résumé Déclaration</h3>

        <div class="total-row">
            <span>TVA due sur opérations:</span>
            <span>{{ number_format(($declaration->grid_54 ?? 0) + ($declaration->grid_55 ?? 0) + ($declaration->grid_56 ?? 0), 2, ',', ' ') }} €</span>
        </div>

        <div class="total-row">
            <span>TVA déductible:</span>
            <span>{{ number_format($declaration->grid_59 ?? 0, 2, ',', ' ') }} €</span>
        </div>

        <div class="total-row main">
            <span>{{ $declaration->amount_to_pay >= 0 ? 'Montant à payer:' : 'Crédit TVA:' }}</span>
            <span>{{ number_format(abs($declaration->amount_to_pay ?? 0), 2, ',', ' ') }} €</span>
        </div>
    </div>

    @if($declaration->notes)
    <div style="margin-top: 20px; padding: 10px; background: #f8f9fa; border-left: 3px solid #0066cc;">
        <strong>Notes:</strong>
        <p>{{ $declaration->notes }}</p>
    </div>
    @endif

    <div class="footer">
        <p>Document généré automatiquement par ComptaBE le {{ now()->format('d/m/Y à H:i') }}</p>
        <p>Ce document est conforme aux exigences du SPF Finances - Belgique</p>
        @if($declaration->status === 'draft')
        <p style="color: #dc3545; font-weight: bold;">⚠️ BROUILLON - Document non-officiel</p>
        @endif
    </div>
</body>
</html>
