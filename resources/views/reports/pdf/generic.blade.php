<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $report['name'] }}</title>
    <style>
        @page {
            margin: 20mm 15mm;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #333;
        }

        .header {
            border-bottom: 2px solid #2563eb;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .header h1 {
            font-size: 20px;
            color: #1e40af;
            margin: 0 0 5px 0;
        }

        .header .meta {
            font-size: 11px;
            color: #666;
        }

        .section {
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #1e40af;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        th, td {
            padding: 8px 10px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }

        th {
            background-color: #f3f4f6;
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
            color: #374151;
        }

        tr:nth-child(even) {
            background-color: #f9fafb;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .font-bold {
            font-weight: bold;
        }

        .total-row {
            background-color: #dbeafe !important;
            font-weight: bold;
        }

        .positive {
            color: #059669;
        }

        .negative {
            color: #dc2626;
        }

        .summary-box {
            background-color: #f3f4f6;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 15px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .summary-row:last-child {
            border-bottom: none;
        }

        .summary-label {
            color: #6b7280;
        }

        .summary-value {
            font-weight: bold;
        }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8px;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
        }

        .page-break {
            page-break-after: always;
        }

        .grid {
            display: table;
            width: 100%;
        }

        .grid-cell {
            display: table-cell;
            padding: 10px;
            vertical-align: top;
        }

        .grid-half {
            width: 50%;
        }
    </style>
</head>
<body>
    <!-- En-tête -->
    <div class="header">
        <h1>{{ $report['name'] }}</h1>
        <div class="meta">
            <strong>{{ $company?->name ?? $report['company'] }}</strong><br>
            @if($report['period']['from'])
                Période: {{ \Carbon\Carbon::parse($report['period']['from'])->format('d/m/Y') }}
                @if($report['period']['to'])
                    - {{ \Carbon\Carbon::parse($report['period']['to'])->format('d/m/Y') }}
                @endif
            @endif
            <br>
            Généré le: {{ \Carbon\Carbon::parse($report['generated_at'])->format('d/m/Y H:i') }}
        </div>
    </div>

    @php $data = $report['data']; @endphp

    <!-- Contenu selon le type de rapport -->
    @if($report['type'] === 'profit_loss')
        {{-- Compte de résultat --}}
        <div class="section">
            <div class="section-title">Revenus</div>
            <table>
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Libellé</th>
                        <th class="text-right">Montant</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['revenue']['items'] ?? [] as $item)
                        <tr>
                            <td>{{ $item['code'] }}</td>
                            <td>{{ $item['label'] }}</td>
                            <td class="text-right">{{ number_format($item['amount'], 2, ',', ' ') }} EUR</td>
                        </tr>
                    @endforeach
                    <tr class="total-row">
                        <td colspan="2">Total Revenus</td>
                        <td class="text-right">{{ number_format($data['revenue']['total'] ?? 0, 2, ',', ' ') }} EUR</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="section">
            <div class="section-title">Charges</div>
            <table>
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Libellé</th>
                        <th class="text-right">Montant</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['expenses']['items'] ?? [] as $item)
                        <tr>
                            <td>{{ $item['code'] }}</td>
                            <td>{{ $item['label'] }}</td>
                            <td class="text-right">{{ number_format($item['amount'], 2, ',', ' ') }} EUR</td>
                        </tr>
                    @endforeach
                    <tr class="total-row">
                        <td colspan="2">Total Charges</td>
                        <td class="text-right">{{ number_format($data['expenses']['total'] ?? 0, 2, ',', ' ') }} EUR</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="summary-box">
            <table>
                <tr>
                    <td>Résultat d'exploitation</td>
                    <td class="text-right font-bold {{ ($data['operating_result'] ?? 0) >= 0 ? 'positive' : 'negative' }}">
                        {{ number_format($data['operating_result'] ?? 0, 2, ',', ' ') }} EUR
                    </td>
                </tr>
                <tr>
                    <td>Résultat financier</td>
                    <td class="text-right font-bold {{ ($data['financial_result'] ?? 0) >= 0 ? 'positive' : 'negative' }}">
                        {{ number_format($data['financial_result'] ?? 0, 2, ',', ' ') }} EUR
                    </td>
                </tr>
                <tr>
                    <td>Résultat exceptionnel</td>
                    <td class="text-right font-bold">
                        {{ number_format($data['exceptional_result'] ?? 0, 2, ',', ' ') }} EUR
                    </td>
                </tr>
                <tr>
                    <td>Impôts</td>
                    <td class="text-right font-bold negative">
                        -{{ number_format($data['taxes'] ?? 0, 2, ',', ' ') }} EUR
                    </td>
                </tr>
                <tr class="total-row">
                    <td><strong>RESULTAT NET</strong></td>
                    <td class="text-right font-bold {{ ($data['net_result'] ?? 0) >= 0 ? 'positive' : 'negative' }}">
                        {{ number_format($data['net_result'] ?? 0, 2, ',', ' ') }} EUR
                    </td>
                </tr>
                <tr>
                    <td>Marge nette</td>
                    <td class="text-right">{{ $data['margin'] ?? 0 }}%</td>
                </tr>
            </table>
        </div>

    @elseif($report['type'] === 'vat_summary')
        {{-- Déclaration TVA --}}
        <div class="section">
            <div class="section-title">Grille TVA</div>
            <table>
                <thead>
                    <tr>
                        <th>Case</th>
                        <th>Libellé</th>
                        <th class="text-right">Montant</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['grid'] ?? [] as $code => $row)
                        <tr>
                            <td class="font-bold">{{ $code }}</td>
                            <td>{{ $row['label'] }}</td>
                            <td class="text-right">{{ number_format($row['amount'], 2, ',', ' ') }} EUR</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="summary-box">
            <h4>Résumé</h4>
            <table>
                <tr>
                    <td>Base ventes</td>
                    <td class="text-right">{{ number_format($data['summary']['sales_base'] ?? 0, 2, ',', ' ') }} EUR</td>
                </tr>
                <tr>
                    <td>TVA sur ventes</td>
                    <td class="text-right">{{ number_format($data['summary']['sales_vat'] ?? 0, 2, ',', ' ') }} EUR</td>
                </tr>
                <tr>
                    <td>Base achats</td>
                    <td class="text-right">{{ number_format($data['summary']['purchases_base'] ?? 0, 2, ',', ' ') }} EUR</td>
                </tr>
                <tr>
                    <td>TVA déductible</td>
                    <td class="text-right">{{ number_format($data['summary']['purchases_vat'] ?? 0, 2, ',', ' ') }} EUR</td>
                </tr>
                <tr class="total-row">
                    <td><strong>{{ ($data['summary']['balance'] ?? 0) >= 0 ? 'TVA à payer' : 'TVA à récupérer' }}</strong></td>
                    <td class="text-right font-bold {{ ($data['summary']['balance'] ?? 0) >= 0 ? 'negative' : 'positive' }}">
                        {{ number_format(abs($data['summary']['balance'] ?? 0), 2, ',', ' ') }} EUR
                    </td>
                </tr>
            </table>
        </div>

    @elseif(in_array($report['type'], ['aged_receivables', 'aged_payables']))
        {{-- Balance âgée --}}
        <div class="section">
            <div class="section-title">Résumé par ancienneté</div>
            <table>
                <thead>
                    <tr>
                        <th>Tranche</th>
                        <th class="text-right">Montant</th>
                        <th class="text-right">Nombre</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['summary'] ?? [] as $bucket => $info)
                        <tr>
                            <td>{{ $info['label'] }}</td>
                            <td class="text-right">{{ number_format($info['amount'], 2, ',', ' ') }} EUR</td>
                            <td class="text-right">{{ $info['count'] }}</td>
                        </tr>
                    @endforeach
                    <tr class="total-row">
                        <td>Total</td>
                        <td class="text-right">{{ number_format($data['total'] ?? 0, 2, ',', ' ') }} EUR</td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>

        @if(!empty($data['by_partner']))
        <div class="section">
            <div class="section-title">Détail par partenaire</div>
            <table>
                <thead>
                    <tr>
                        <th>Partenaire</th>
                        <th class="text-right">0-30j</th>
                        <th class="text-right">31-60j</th>
                        <th class="text-right">61-90j</th>
                        <th class="text-right">>90j</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['by_partner'] as $partner)
                        <tr>
                            <td>{{ $partner['partner'] }}</td>
                            <td class="text-right">{{ number_format($partner['current'] ?? 0, 2, ',', ' ') }}</td>
                            <td class="text-right">{{ number_format($partner['days_30'] ?? 0, 2, ',', ' ') }}</td>
                            <td class="text-right">{{ number_format($partner['days_60'] ?? 0, 2, ',', ' ') }}</td>
                            <td class="text-right">{{ number_format($partner['days_90'] ?? 0, 2, ',', ' ') }}</td>
                            <td class="text-right font-bold">{{ number_format($partner['total'] ?? 0, 2, ',', ' ') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

    @elseif($report['type'] === 'trial_balance')
        {{-- Balance des comptes --}}
        <div class="section">
            <div class="section-title">Balance des comptes</div>
            <table>
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Libellé</th>
                        <th class="text-right">Débit</th>
                        <th class="text-right">Crédit</th>
                        <th class="text-right">Solde D</th>
                        <th class="text-right">Solde C</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['accounts'] ?? [] as $account)
                        <tr>
                            <td>{{ $account['code'] }}</td>
                            <td>{{ $account['name'] }}</td>
                            <td class="text-right">{{ number_format($account['debit'], 2, ',', ' ') }}</td>
                            <td class="text-right">{{ number_format($account['credit'], 2, ',', ' ') }}</td>
                            <td class="text-right">{{ number_format($account['balance_debit'], 2, ',', ' ') }}</td>
                            <td class="text-right">{{ number_format($account['balance_credit'], 2, ',', ' ') }}</td>
                        </tr>
                    @endforeach
                    <tr class="total-row">
                        <td colspan="2">TOTAUX</td>
                        <td class="text-right">{{ number_format($data['totals']['debit'] ?? 0, 2, ',', ' ') }}</td>
                        <td class="text-right">{{ number_format($data['totals']['credit'] ?? 0, 2, ',', ' ') }}</td>
                        <td class="text-right">{{ number_format($data['totals']['balance_debit'] ?? 0, 2, ',', ' ') }}</td>
                        <td class="text-right">{{ number_format($data['totals']['balance_credit'] ?? 0, 2, ',', ' ') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

    @elseif(in_array($report['type'], ['sales_report', 'purchase_report']))
        {{-- Rapports ventes/achats --}}
        <div class="section">
            <div class="section-title">Totaux</div>
            <div class="summary-box">
                <table>
                    <tr>
                        <td>Nombre de factures</td>
                        <td class="text-right font-bold">{{ $data['totals']['count'] ?? 0 }}</td>
                    </tr>
                    <tr>
                        <td>Total HT</td>
                        <td class="text-right">{{ number_format($data['totals']['total_excl_vat'] ?? 0, 2, ',', ' ') }} EUR</td>
                    </tr>
                    <tr>
                        <td>Total TVA</td>
                        <td class="text-right">{{ number_format($data['totals']['vat'] ?? 0, 2, ',', ' ') }} EUR</td>
                    </tr>
                    <tr class="total-row">
                        <td><strong>Total TTC</strong></td>
                        <td class="text-right font-bold">{{ number_format($data['totals']['total'] ?? 0, 2, ',', ' ') }} EUR</td>
                    </tr>
                </table>
            </div>
        </div>

        @if(!empty($data['invoices']))
        <div class="section">
            <div class="section-title">Liste des factures</div>
            <table>
                <thead>
                    <tr>
                        <th>Numéro</th>
                        <th>Date</th>
                        <th>Partenaire</th>
                        <th class="text-right">Montant</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['invoices'] as $invoice)
                        <tr>
                            <td>{{ $invoice['number'] }}</td>
                            <td>{{ \Carbon\Carbon::parse($invoice['date'])->format('d/m/Y') }}</td>
                            <td>{{ $invoice['partner'] }}</td>
                            <td class="text-right">{{ number_format($invoice['total'], 2, ',', ' ') }} EUR</td>
                            <td>{{ ucfirst($invoice['status']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

    @else
        {{-- Rapport générique --}}
        @if(is_array($data))
            @foreach($data as $key => $value)
                @if(is_array($value))
                    <div class="section">
                        <div class="section-title">{{ ucfirst(str_replace('_', ' ', $key)) }}</div>
                        @if(isset($value[0]) && is_array($value[0]))
                            <table>
                                <thead>
                                    <tr>
                                        @foreach(array_keys($value[0]) as $header)
                                            <th>{{ ucfirst(str_replace('_', ' ', $header)) }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($value as $row)
                                        <tr>
                                            @foreach($row as $cell)
                                                <td>{{ is_array($cell) ? json_encode($cell) : $cell }}</td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <table>
                                @foreach($value as $k => $v)
                                    <tr>
                                        <td>{{ ucfirst(str_replace('_', ' ', $k)) }}</td>
                                        <td class="text-right">{{ is_array($v) ? json_encode($v) : $v }}</td>
                                    </tr>
                                @endforeach
                            </table>
                        @endif
                    </div>
                @endif
            @endforeach
        @endif
    @endif

    <!-- Pied de page -->
    <div class="footer">
        {{ $company?->name ?? $report['company'] }} - {{ $report['name'] }} - Généré le {{ \Carbon\Carbon::parse($report['generated_at'])->format('d/m/Y à H:i') }}
    </div>
</body>
</html>
