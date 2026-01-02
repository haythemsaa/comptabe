<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Fiche de Paie - {{ $payslip->employee_name }} - {{ $payslip->period->format('m/Y') }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.3;
            color: #333;
        }

        .header {
            display: table;
            width: 100%;
            margin-bottom: 20px;
            border-bottom: 3px solid #0066cc;
            padding-bottom: 10px;
        }

        .header .left,
        .header .right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }

        .header h1 {
            color: #0066cc;
            font-size: 18pt;
            margin-bottom: 5px;
        }

        .company-box,
        .employee-box {
            background: #f8f9fa;
            padding: 10px;
            margin-bottom: 15px;
            border-left: 4px solid #0066cc;
        }

        .company-box h3,
        .employee-box h3 {
            color: #0066cc;
            font-size: 11pt;
            margin-bottom: 5px;
        }

        .company-box p,
        .employee-box p {
            margin: 2px 0;
            font-size: 9pt;
        }

        .period-info {
            text-align: center;
            background: #0066cc;
            color: white;
            padding: 10px;
            margin: 15px 0;
            font-weight: bold;
            font-size: 12pt;
        }

        .amounts-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 9pt;
        }

        .amounts-table thead {
            background: #0066cc;
            color: white;
        }

        .amounts-table th {
            padding: 8px 5px;
            text-align: left;
            font-weight: bold;
        }

        .amounts-table td {
            padding: 6px 5px;
            border-bottom: 1px solid #e0e0e0;
        }

        .amounts-table tbody tr:nth-child(even) {
            background: #f8f9fa;
        }

        .amounts-table .amount {
            text-align: right;
            font-family: 'Courier New', monospace;
        }

        .amounts-table .category-header {
            background: #e7f3ff;
            font-weight: bold;
            padding: 8px 5px;
        }

        .totals {
            margin-top: 20px;
            background: #fff3cd;
            border: 2px solid #ffc107;
            padding: 15px;
        }

        .totals .row {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            font-size: 11pt;
        }

        .totals .row.main {
            font-weight: bold;
            font-size: 14pt;
            border-top: 2px solid #856404;
            padding-top: 10px;
            margin-top: 10px;
            color: #0066cc;
        }

        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            font-size: 8pt;
            color: #666;
        }

        .signatures {
            display: table;
            width: 100%;
            margin-top: 40px;
        }

        .signatures .col {
            display: table-cell;
            width: 50%;
            text-align: center;
            padding: 10px;
        }

        .signatures .signature-line {
            margin-top: 50px;
            border-top: 1px solid #333;
            padding-top: 5px;
            font-size: 9pt;
        }

        @page {
            margin: 1.5cm;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="left">
            <h1>FICHE DE PAIE</h1>
        </div>
        <div class="right" style="text-align: right;">
            <p><strong>N° Fiche:</strong> {{ $payslip->payslip_number }}</p>
            <p><strong>Date d'émission:</strong> {{ $payslip->issue_date->format('d/m/Y') }}</p>
        </div>
    </div>

    <div class="company-box">
        <h3>EMPLOYEUR</h3>
        <p><strong>{{ $company->name }}</strong></p>
        <p>{{ $company->address }}</p>
        <p>{{ $company->postal_code }} {{ $company->city }}</p>
        <p>N° TVA: {{ $company->vat_number }}</p>
        <p>N° {{ $companySocialSecurityOrg }}: {{ $company->onss_number ?? $company->cnss_employer_number ?? 'N/A' }}</p>
    </div>

    <div class="employee-box">
        <h3>EMPLOYÉ</h3>
        <p><strong>{{ $payslip->employee_name }}</strong></p>
        <p>N° Registre National: {{ $payslip->employee_national_number ?? 'N/A' }}</p>
        <p>Fonction: {{ $payslip->job_title ?? 'N/A' }}</p>
        <p>Date d'entrée: {{ $payslip->hire_date ? $payslip->hire_date->format('d/m/Y') : 'N/A' }}</p>
    </div>

    <div class="period-info">
        PÉRIODE: {{ $payslip->period->format('F Y') }}
        ({{ $payslip->work_days ?? 20 }} jours travaillés)
    </div>

    <table class="amounts-table">
        <thead>
            <tr>
                <th style="width: 50%;">Description</th>
                <th style="width: 15%;" class="amount">Base</th>
                <th style="width: 10%;" class="amount">Taux</th>
                <th style="width: 25%;" class="amount">Montant</th>
            </tr>
        </thead>
        <tbody>
            <!-- Salaire de base -->
            <tr class="category-header">
                <td colspan="4">RÉMUNÉRATION BRUTE</td>
            </tr>
            <tr>
                <td>Salaire de base</td>
                <td class="amount">{{ number_format($payslip->base_salary ?? 0, 2, ',', ' ') }} €</td>
                <td class="amount">-</td>
                <td class="amount">{{ number_format($payslip->base_salary ?? 0, 2, ',', ' ') }} €</td>
            </tr>

            @if(($payslip->overtime_hours ?? 0) > 0)
            <tr>
                <td>Heures supplémentaires ({{ $payslip->overtime_hours }}h)</td>
                <td class="amount">{{ number_format($payslip->overtime_rate ?? 0, 2, ',', ' ') }} €/h</td>
                <td class="amount">-</td>
                <td class="amount">{{ number_format(($payslip->overtime_hours ?? 0) * ($payslip->overtime_rate ?? 0), 2, ',', ' ') }} €</td>
            </tr>
            @endif

            @if(($payslip->bonuses ?? 0) > 0)
            <tr>
                <td>Primes et bonus</td>
                <td class="amount">-</td>
                <td class="amount">-</td>
                <td class="amount">{{ number_format($payslip->bonuses ?? 0, 2, ',', ' ') }} €</td>
            </tr>
            @endif

            <!-- Cotisations sociales -->
            <tr class="category-header">
                <td colspan="4">COTISATIONS SOCIALES EMPLOYÉ</td>
            </tr>
            <tr>
                <td>Sécurité sociale {{ $companySocialSecurityOrg }}</td>
                <td class="amount">{{ number_format($payslip->gross_salary ?? 0, 2, ',', ' ') }} €</td>
                <td class="amount">13.07%</td>
                <td class="amount">-{{ number_format($payslip->social_security_employee ?? 0, 2, ',', ' ') }} €</td>
            </tr>

            <!-- Précompte professionnel -->
            <tr class="category-header">
                <td colspan="4">PRÉCOMPTE PROFESSIONNEL</td>
            </tr>
            <tr>
                <td>Impôt sur le revenu</td>
                <td class="amount">{{ number_format($payslip->taxable_income ?? 0, 2, ',', ' ') }} €</td>
                <td class="amount">{{ number_format(($payslip->tax_withheld ?? 0) / ($payslip->taxable_income ?? 1) * 100, 2, ',', ' ') }}%</td>
                <td class="amount">-{{ number_format($payslip->tax_withheld ?? 0, 2, ',', ' ') }} €</td>
            </tr>

            <!-- Autres déductions -->
            @if(($payslip->other_deductions ?? 0) > 0)
            <tr class="category-header">
                <td colspan="4">AUTRES DÉDUCTIONS</td>
            </tr>
            <tr>
                <td>{{ $payslip->deduction_description ?? 'Autres déductions' }}</td>
                <td class="amount">-</td>
                <td class="amount">-</td>
                <td class="amount">-{{ number_format($payslip->other_deductions ?? 0, 2, ',', ' ') }} €</td>
            </tr>
            @endif
        </tbody>
    </table>

    <div class="totals">
        <div class="row">
            <span>Rémunération brute:</span>
            <span>{{ number_format($payslip->gross_salary ?? 0, 2, ',', ' ') }} €</span>
        </div>

        <div class="row">
            <span>Cotisations sociales (13.07%):</span>
            <span>-{{ number_format($payslip->social_security_employee ?? 0, 2, ',', ' ') }} €</span>
        </div>

        <div class="row">
            <span>Précompte professionnel:</span>
            <span>-{{ number_format($payslip->tax_withheld ?? 0, 2, ',', ' ') }} €</span>
        </div>

        @if(($payslip->other_deductions ?? 0) > 0)
        <div class="row">
            <span>Autres déductions:</span>
            <span>-{{ number_format($payslip->other_deductions ?? 0, 2, ',', ' ') }} €</span>
        </div>
        @endif

        <div class="row main">
            <span>SALAIRE NET À PAYER:</span>
            <span>{{ number_format($payslip->net_salary ?? 0, 2, ',', ' ') }} €</span>
        </div>
    </div>

    <div style="margin-top: 20px; padding: 10px; background: #f8f9fa; border-left: 3px solid #28a745;">
        <p><strong>Paiement:</strong></p>
        <p>Date de paiement: {{ $payslip->payment_date ? $payslip->payment_date->format('d/m/Y') : 'À définir' }}</p>
        <p>Mode de paiement: {{ $payslip->payment_method ?? 'Virement bancaire' }}</p>
        @if($payslip->bank_account)
        <p>Compte bancaire: {{ $payslip->bank_account }}</p>
        @endif
    </div>

    <div class="signatures">
        <div class="col">
            <p><strong>L'employeur</strong></p>
            <div class="signature-line">Signature et cachet</div>
        </div>
        <div class="col">
            <p><strong>L'employé</strong></p>
            <div class="signature-line">Signature pour réception</div>
        </div>
    </div>

    <div class="footer">
        <p><strong>IMPORTANT:</strong> Cette fiche de paie doit être conservée sans limitation de durée.</p>
        <p>Pour toute question concernant cette fiche de paie, contactez le service RH: {{ $company->email ?? 'N/A' }}</p>
        <p style="margin-top: 10px; text-align: center; color: #999;">
            Document généré automatiquement par ComptaBE - {{ now()->format('d/m/Y H:i') }}
        </p>
    </div>
</body>
</html>
