<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Facture {{ $invoice->invoice_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10pt;
            line-height: 1.4;
            color: #1e293b;
            background: white;
        }
        .page { padding: 0; }

        /* Modern Theme - Top accent band */
        .accent-band {
            height: 8px;
            background: linear-gradient(90deg, {{ $templateColors['primary'] ?? '#6366f1' }}, {{ $templateColors['secondary'] ?? '#1e293b' }});
        }
        .content { padding: 30px 40px; }

        /* Header */
        .header {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }
        .header-left {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        .header-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            text-align: right;
        }
        .company-logo { max-width: 150px; max-height: 60px; margin-bottom: 10px; }
        .company-name {
            font-size: 18pt;
            font-weight: bold;
            color: {{ $templateColors['primary'] ?? '#6366f1' }};
            margin-bottom: 5px;
        }
        .company-info { font-size: 9pt; color: #64748b; line-height: 1.6; }

        .invoice-badge {
            display: inline-block;
            background: {{ $templateColors['primary'] ?? '#6366f1' }};
            color: white;
            padding: 8px 20px;
            border-radius: 25px;
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 10px;
        }
        .invoice-number {
            font-size: 11pt;
            color: {{ $templateColors['secondary'] ?? '#1e293b' }};
            font-weight: 600;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 10px;
        }
        .status-draft { background: #fef3c7; color: #92400e; }
        .status-validated { background: #dbeafe; color: #1e40af; }
        .status-sent { background: #e0e7ff; color: #3730a3; }
        .status-paid { background: #d1fae5; color: #065f46; }
        .status-overdue { background: #fee2e2; color: #991b1b; }

        /* Addresses - Modern cards */
        .addresses {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }
        .address-block {
            display: table-cell;
            width: 48%;
            padding: 18px;
            background: #f8fafc;
            border-radius: 12px;
            border-left: 4px solid {{ $templateColors['primary'] ?? '#6366f1' }};
        }
        .address-spacer { display: table-cell; width: 4%; }
        .address-title {
            font-size: 8pt;
            text-transform: uppercase;
            color: {{ $templateColors['primary'] ?? '#6366f1' }};
            font-weight: bold;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }
        .address-name { font-weight: bold; font-size: 11pt; margin-bottom: 5px; color: {{ $templateColors['secondary'] ?? '#1e293b' }}; }
        .address-details { font-size: 9pt; color: #64748b; line-height: 1.6; }

        /* Info Grid - Modern pill style */
        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 30px;
            background: linear-gradient(135deg, {{ $templateColors['primary'] ?? '#6366f1' }}15, {{ $templateColors['primary'] ?? '#6366f1' }}05);
            border-radius: 12px;
            padding: 15px;
        }
        .info-item {
            display: table-cell;
            text-align: center;
            padding: 8px 15px;
        }
        .info-label {
            font-size: 7pt;
            text-transform: uppercase;
            color: {{ $templateColors['primary'] ?? '#6366f1' }};
            margin-bottom: 4px;
            letter-spacing: 0.5px;
            font-weight: 600;
        }
        .info-value { font-size: 10pt; font-weight: bold; color: {{ $templateColors['secondary'] ?? '#1e293b' }}; }

        /* Table - Modern style */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .items-table thead th {
            background: {{ $templateColors['secondary'] ?? '#1e293b' }};
            color: white;
            padding: 12px 15px;
            text-align: left;
            font-size: 8pt;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .items-table thead th:first-child { border-radius: 10px 0 0 0; }
        .items-table thead th:last-child { border-radius: 0 10px 0 0; text-align: right; }
        .items-table tbody td {
            padding: 14px 15px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: top;
            font-size: 9pt;
        }
        .items-table tbody tr:hover td { background: #f8fafc; }
        .items-table .text-right { text-align: right; }
        .items-table .text-center { text-align: center; }
        .item-description { font-weight: 500; color: {{ $templateColors['secondary'] ?? '#1e293b' }}; }
        .item-discount { font-size: 8pt; color: {{ $templateColors['primary'] ?? '#6366f1' }}; margin-top: 3px; }

        /* Totals - Modern rounded */
        .totals-section { display: table; width: 100%; margin-bottom: 30px; }
        .totals-spacer { display: table-cell; width: 55%; }
        .totals-table { display: table-cell; width: 45%; }
        .totals-row {
            display: table;
            width: 100%;
            margin-bottom: 6px;
        }
        .totals-label {
            display: table-cell;
            width: 55%;
            padding: 8px 12px;
            text-align: right;
            color: #64748b;
            font-size: 9pt;
        }
        .totals-value {
            display: table-cell;
            width: 45%;
            padding: 8px 12px;
            text-align: right;
            font-weight: 500;
            font-size: 9pt;
        }
        .totals-total {
            background: {{ $templateColors['primary'] ?? '#6366f1' }};
            color: white;
            border-radius: 10px;
            margin-top: 8px;
        }
        .totals-total .totals-label {
            color: rgba(255,255,255,0.85);
            font-size: 11pt;
        }
        .totals-total .totals-value {
            font-size: 14pt;
            font-weight: bold;
        }

        /* Payment Info - Modern card */
        .payment-section {
            background: #f8fafc;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 30px;
        }
        .payment-title {
            font-size: 11pt;
            font-weight: bold;
            margin-bottom: 15px;
            color: {{ $templateColors['secondary'] ?? '#1e293b' }};
            display: inline-block;
            padding-bottom: 8px;
            border-bottom: 2px solid {{ $templateColors['primary'] ?? '#6366f1' }};
        }
        .payment-container { display: table; width: 100%; }
        .payment-grid { display: table-cell; width: 60%; vertical-align: top; }
        .payment-item { margin-bottom: 12px; }
        .payment-label {
            font-size: 7pt;
            text-transform: uppercase;
            color: {{ $templateColors['primary'] ?? '#6366f1' }};
            margin-bottom: 3px;
            font-weight: 600;
        }
        .payment-value { font-family: 'DejaVu Sans Mono', monospace; font-size: 9pt; color: #1e293b; }
        .payment-qr { display: table-cell; width: 40%; text-align: right; vertical-align: top; }
        .qr-code-container {
            display: inline-block;
            padding: 12px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .qr-code-label { font-size: 8pt; text-align: center; color: #64748b; margin-top: 8px; }

        /* Notes */
        .notes-section { margin-bottom: 30px; }
        .notes-title {
            font-size: 9pt;
            text-transform: uppercase;
            color: {{ $templateColors['primary'] ?? '#6366f1' }};
            margin-bottom: 8px;
            font-weight: 600;
        }
        .notes-content { font-size: 9pt; color: #64748b; white-space: pre-line; }

        /* Footer */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8pt;
            color: white;
            background: {{ $templateColors['secondary'] ?? '#1e293b' }};
            padding: 12px 40px;
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="accent-band"></div>
        <div class="content">
            <!-- Header -->
            <div class="header">
                <div class="header-left">
                    @if($company->logo_path)
                        <img src="{{ storage_path('app/public/' . $company->logo_path) }}" alt="{{ $company->name }}" class="company-logo">
                    @endif
                    <div class="company-name">{{ $company->name }}</div>
                    <div class="company-info">
                        @if($company->street){{ $company->street }} {{ $company->house_number }}<br>@endif
                        @if($company->postal_code || $company->city){{ $company->postal_code }} {{ $company->city }}<br>@endif
                        @if($company->vat_number)TVA: {{ $company->formatted_vat_number }}<br>@endif
                        @if($company->email){{ $company->email }}@endif
                        @if($company->phone) | {{ $company->phone }}@endif
                    </div>
                </div>
                <div class="header-right">
                    <span class="invoice-badge">Facture</span>
                    <div class="invoice-number">{{ $invoice->invoice_number }}</div>
                    <span class="status-badge status-{{ $invoice->status }}">{{ $invoice->status_label }}</span>
                </div>
            </div>

            <!-- Addresses -->
            <div class="addresses">
                <div class="address-block">
                    <div class="address-title">Facturer a</div>
                    <div class="address-name">{{ $invoice->partner->name }}</div>
                    <div class="address-details">
                        @if($invoice->partner->street){{ $invoice->partner->street }} {{ $invoice->partner->house_number }}<br>@endif
                        @if($invoice->partner->postal_code || $invoice->partner->city){{ $invoice->partner->postal_code }} {{ $invoice->partner->city }}<br>@endif
                        @if($invoice->partner->country_code && $invoice->partner->country_code !== 'BE'){{ $invoice->partner->country_code }}<br>@endif
                        @if($invoice->partner->vat_number)TVA: {{ $invoice->partner->vat_number }}@endif
                    </div>
                </div>
                <div class="address-spacer"></div>
                <div class="address-block">
                    <div class="address-title">Livrer a</div>
                    <div class="address-name">{{ $invoice->partner->name }}</div>
                    <div class="address-details">
                        @if($invoice->partner->street){{ $invoice->partner->street }} {{ $invoice->partner->house_number }}<br>@endif
                        @if($invoice->partner->postal_code || $invoice->partner->city){{ $invoice->partner->postal_code }} {{ $invoice->partner->city }}@endif
                    </div>
                </div>
            </div>

            <!-- Info Grid -->
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Date de facture</div>
                    <div class="info-value">{{ $invoice->invoice_date->format('d/m/Y') }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Date d'echeance</div>
                    <div class="info-value">{{ $invoice->due_date ? $invoice->due_date->format('d/m/Y') : '-' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Reference</div>
                    <div class="info-value">{{ $invoice->reference ?? '-' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Communication</div>
                    <div class="info-value" style="font-family: 'DejaVu Sans Mono', monospace;">{{ $invoice->structured_communication }}</div>
                </div>
            </div>

            <!-- Items Table -->
            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width: 45%;">Description</th>
                        <th class="text-center" style="width: 10%;">Qte</th>
                        <th class="text-right" style="width: 15%;">Prix unit.</th>
                        <th class="text-center" style="width: 10%;">TVA</th>
                        <th class="text-right" style="width: 20%;">Total HT</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->lines as $line)
                        <tr>
                            <td>
                                <div class="item-description">{{ $line->description }}</div>
                                @if($line->discount_percent > 0)
                                    <div class="item-discount">-{{ number_format($line->discount_percent, 2, ',', ' ') }}% remise</div>
                                @endif
                            </td>
                            <td class="text-center">{{ number_format($line->quantity, 2, ',', ' ') }}</td>
                            <td class="text-right">{{ number_format($line->unit_price, 2, ',', ' ') }} EUR</td>
                            <td class="text-center">{{ number_format($line->vat_rate, 0) }}%</td>
                            <td class="text-right">{{ number_format($line->total_excl_vat, 2, ',', ' ') }} EUR</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Totals -->
            <div class="totals-section">
                <div class="totals-spacer"></div>
                <div class="totals-table">
                    <div class="totals-row">
                        <div class="totals-label">Sous-total HT</div>
                        <div class="totals-value">{{ number_format($invoice->total_excl_vat, 2, ',', ' ') }} EUR</div>
                    </div>
                    @foreach($invoice->vatSummary() as $rate => $amount)
                        <div class="totals-row">
                            <div class="totals-label">TVA {{ $rate }}%</div>
                            <div class="totals-value">{{ number_format($amount, 2, ',', ' ') }} EUR</div>
                        </div>
                    @endforeach
                    <div class="totals-row totals-total">
                        <div class="totals-label">Total TTC</div>
                        <div class="totals-value">{{ number_format($invoice->total_incl_vat, 2, ',', ' ') }} EUR</div>
                    </div>
                </div>
            </div>

            <!-- Payment Info -->
            <div class="payment-section">
                <div class="payment-title">Informations de paiement</div>
                <div class="payment-container">
                    <div class="payment-grid">
                        <div class="payment-item">
                            <div class="payment-label">Beneficiaire</div>
                            <div class="payment-value">{{ $company->name }}</div>
                        </div>
                        <div class="payment-item">
                            <div class="payment-label">IBAN</div>
                            <div class="payment-value">{{ $company->formatted_iban ?? '-' }}</div>
                        </div>
                        @if($company->default_bic)
                        <div class="payment-item">
                            <div class="payment-label">BIC</div>
                            <div class="payment-value">{{ $company->default_bic }}</div>
                        </div>
                        @endif
                        <div class="payment-item">
                            <div class="payment-label">Communication structuree</div>
                            <div class="payment-value">{{ $invoice->structured_communication }}</div>
                        </div>
                        <div class="payment-item">
                            <div class="payment-label">Montant a payer</div>
                            <div class="payment-value" style="font-weight: bold; font-size: 12pt; color: {{ $templateColors['primary'] ?? '#6366f1' }};">{{ number_format($invoice->amount_due, 2, ',', ' ') }} EUR</div>
                        </div>
                    </div>
                    @if($company->default_iban && $invoice->amount_due > 0 && isset($qrCode))
                    <div class="payment-qr">
                        <div class="qr-code-container">
                            <img src="data:image/png;base64,{{ $qrCode }}" alt="QR Code Paiement" style="width: 130px; height: 130px; display: block;">
                        </div>
                        <div class="qr-code-label">Scannez pour payer</div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Notes -->
            @if($invoice->notes)
                <div class="notes-section">
                    <div class="notes-title">Notes</div>
                    <div class="notes-content">{{ $invoice->notes }}</div>
                </div>
            @endif
        </div>

        <!-- Footer -->
        <div class="footer">
            {{ $company->name }} | TVA: {{ $company->formatted_vat_number ?? '' }} | IBAN: {{ $company->formatted_iban ?? '' }}
            @if($company->default_bic) | BIC: {{ $company->default_bic }}@endif
        </div>
    </div>
</body>
</html>
