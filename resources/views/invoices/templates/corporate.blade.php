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

        /* Corporate header band */
        .header-band {
            background: {{ $templateColors['primary'] ?? '#0369a1' }};
            padding: 25px 40px;
            color: white;
        }
        .header-band-content {
            display: table;
            width: 100%;
        }
        .header-band-left {
            display: table-cell;
            width: 60%;
            vertical-align: middle;
        }
        .header-band-right {
            display: table-cell;
            width: 40%;
            vertical-align: middle;
            text-align: right;
        }
        .company-logo { max-width: 140px; max-height: 50px; margin-bottom: 8px; }
        .company-name {
            font-size: 16pt;
            font-weight: bold;
            margin-bottom: 3px;
        }
        .company-tagline {
            font-size: 8pt;
            opacity: 0.85;
            letter-spacing: 1px;
        }
        .invoice-label {
            font-size: 9pt;
            opacity: 0.8;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .invoice-number {
            font-size: 20pt;
            font-weight: bold;
            margin-top: 3px;
        }

        .content { padding: 30px 40px; }

        /* Sub-header info */
        .sub-header {
            display: table;
            width: 100%;
            margin-bottom: 25px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
        }
        .sub-header-item {
            display: table-cell;
            padding: 12px 15px;
            text-align: center;
            border-right: 1px solid #e2e8f0;
        }
        .sub-header-item:last-child { border-right: none; }
        .sub-header-label {
            font-size: 7pt;
            text-transform: uppercase;
            color: {{ $templateColors['secondary'] ?? '#334155' }};
            letter-spacing: 0.5px;
            margin-bottom: 3px;
        }
        .sub-header-value { font-size: 9pt; font-weight: 600; color: {{ $templateColors['primary'] ?? '#0369a1' }}; }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            font-size: 7pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-radius: 2px;
        }
        .status-draft { background: #fef3c7; color: #92400e; }
        .status-validated { background: #dbeafe; color: #1e40af; }
        .status-sent { background: #e0e7ff; color: #3730a3; }
        .status-paid { background: #d1fae5; color: #065f46; }
        .status-overdue { background: #fee2e2; color: #991b1b; }

        /* Two column layout */
        .two-columns {
            display: table;
            width: 100%;
            margin-bottom: 25px;
        }
        .column-left {
            display: table-cell;
            width: 48%;
            vertical-align: top;
        }
        .column-spacer { display: table-cell; width: 4%; }
        .column-right {
            display: table-cell;
            width: 48%;
            vertical-align: top;
        }

        /* Company details box */
        .details-box {
            border: 1px solid #e2e8f0;
            padding: 15px;
            margin-bottom: 15px;
        }
        .details-box-title {
            font-size: 8pt;
            text-transform: uppercase;
            color: white;
            background: {{ $templateColors['primary'] ?? '#0369a1' }};
            padding: 5px 10px;
            margin: -15px -15px 12px -15px;
            letter-spacing: 1px;
        }
        .details-row {
            display: table;
            width: 100%;
            margin-bottom: 5px;
        }
        .details-label {
            display: table-cell;
            width: 35%;
            font-size: 8pt;
            color: {{ $templateColors['secondary'] ?? '#334155' }};
        }
        .details-value {
            display: table-cell;
            font-size: 9pt;
            color: #1e293b;
        }

        /* Address block */
        .address-block { padding: 15px; border: 1px solid #e2e8f0; }
        .address-title {
            font-size: 8pt;
            text-transform: uppercase;
            color: white;
            background: {{ $templateColors['secondary'] ?? '#334155' }};
            padding: 5px 10px;
            margin: -15px -15px 12px -15px;
            letter-spacing: 1px;
        }
        .address-name { font-weight: bold; font-size: 10pt; margin-bottom: 5px; color: #1e293b; }
        .address-details { font-size: 9pt; color: #64748b; line-height: 1.6; }

        /* Table - Corporate style */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
            border: 1px solid #e2e8f0;
        }
        .items-table thead th {
            background: {{ $templateColors['secondary'] ?? '#334155' }};
            color: white;
            padding: 10px 12px;
            text-align: left;
            font-size: 8pt;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .items-table thead th:last-child { text-align: right; }
        .items-table tbody td {
            padding: 10px 12px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: top;
            font-size: 9pt;
        }
        .items-table tbody tr:nth-child(even) td { background: #f8fafc; }
        .items-table .text-right { text-align: right; }
        .items-table .text-center { text-align: center; }
        .item-description { font-weight: 500; }
        .item-discount { font-size: 8pt; color: #64748b; }

        /* Totals - Corporate */
        .totals-section { display: table; width: 100%; margin-bottom: 25px; }
        .totals-spacer { display: table-cell; width: 55%; }
        .totals-table {
            display: table-cell;
            width: 45%;
            border: 1px solid #e2e8f0;
        }
        .totals-row { display: table; width: 100%; }
        .totals-label {
            display: table-cell;
            width: 55%;
            padding: 8px 12px;
            text-align: right;
            color: {{ $templateColors['secondary'] ?? '#334155' }};
            font-size: 9pt;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
        }
        .totals-value {
            display: table-cell;
            width: 45%;
            padding: 8px 12px;
            text-align: right;
            font-weight: 500;
            font-size: 9pt;
            border-bottom: 1px solid #e2e8f0;
        }
        .totals-total {
            background: {{ $templateColors['primary'] ?? '#0369a1' }};
            color: white;
        }
        .totals-total .totals-label {
            background: {{ $templateColors['primary'] ?? '#0369a1' }};
            color: rgba(255,255,255,0.85);
            font-size: 10pt;
            border-bottom: none;
        }
        .totals-total .totals-value {
            font-size: 13pt;
            font-weight: bold;
            border-bottom: none;
        }

        /* Payment Info - Corporate */
        .payment-section {
            display: table;
            width: 100%;
            margin-bottom: 25px;
        }
        .payment-details {
            display: table-cell;
            width: 65%;
            padding: 15px;
            border: 1px solid #e2e8f0;
            vertical-align: top;
        }
        .payment-spacer { display: table-cell; width: 3%; }
        .payment-qr-section {
            display: table-cell;
            width: 32%;
            padding: 15px;
            border: 1px solid #e2e8f0;
            text-align: center;
            vertical-align: top;
        }
        .payment-title {
            font-size: 8pt;
            text-transform: uppercase;
            color: white;
            background: {{ $templateColors['primary'] ?? '#0369a1' }};
            padding: 5px 10px;
            margin: -15px -15px 12px -15px;
            letter-spacing: 1px;
        }
        .payment-row { display: table; width: 100%; margin-bottom: 8px; }
        .payment-label {
            display: table-cell;
            width: 35%;
            font-size: 8pt;
            color: {{ $templateColors['secondary'] ?? '#334155' }};
        }
        .payment-value {
            display: table-cell;
            font-family: 'DejaVu Sans Mono', monospace;
            font-size: 9pt;
            color: #1e293b;
        }
        .qr-title {
            font-size: 8pt;
            text-transform: uppercase;
            color: white;
            background: {{ $templateColors['secondary'] ?? '#334155' }};
            padding: 5px 10px;
            margin: -15px -15px 12px -15px;
            letter-spacing: 1px;
        }
        .qr-code-container {
            display: inline-block;
            padding: 8px;
            background: white;
            border: 1px solid #e2e8f0;
        }
        .qr-code-label { font-size: 7pt; color: #64748b; margin-top: 8px; }

        /* Notes */
        .notes-section {
            border: 1px solid #e2e8f0;
            padding: 15px;
            margin-bottom: 25px;
        }
        .notes-title {
            font-size: 8pt;
            text-transform: uppercase;
            color: white;
            background: {{ $templateColors['secondary'] ?? '#334155' }};
            padding: 5px 10px;
            margin: -15px -15px 12px -15px;
            letter-spacing: 1px;
        }
        .notes-content { font-size: 9pt; color: #64748b; white-space: pre-line; }

        /* Footer */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: {{ $templateColors['secondary'] ?? '#334155' }};
            color: white;
            padding: 10px 40px;
            font-size: 7pt;
        }
        .footer-content {
            display: table;
            width: 100%;
        }
        .footer-left {
            display: table-cell;
            width: 70%;
        }
        .footer-right {
            display: table-cell;
            width: 30%;
            text-align: right;
            color: rgba(255,255,255,0.7);
        }
    </style>
</head>
<body>
    <div class="page">
        <!-- Header Band -->
        <div class="header-band">
            <div class="header-band-content">
                <div class="header-band-left">
                    @if($company->logo_path)
                        <img src="{{ storage_path('app/public/' . $company->logo_path) }}" alt="{{ $company->name }}" class="company-logo">
                    @endif
                    <div class="company-name">{{ $company->name }}</div>
                    @if($company->vat_number)
                        <div class="company-tagline">TVA: {{ $company->formatted_vat_number }}</div>
                    @endif
                </div>
                <div class="header-band-right">
                    <div class="invoice-label">Facture</div>
                    <div class="invoice-number">{{ $invoice->invoice_number }}</div>
                    <span class="status-badge status-{{ $invoice->status }}">{{ $invoice->status_label }}</span>
                </div>
            </div>
        </div>

        <div class="content">
            <!-- Sub-header with key info -->
            <div class="sub-header">
                <div class="sub-header-item">
                    <div class="sub-header-label">Date de facture</div>
                    <div class="sub-header-value">{{ $invoice->invoice_date->format('d/m/Y') }}</div>
                </div>
                <div class="sub-header-item">
                    <div class="sub-header-label">Date d'echeance</div>
                    <div class="sub-header-value">{{ $invoice->due_date ? $invoice->due_date->format('d/m/Y') : '-' }}</div>
                </div>
                <div class="sub-header-item">
                    <div class="sub-header-label">Reference</div>
                    <div class="sub-header-value">{{ $invoice->reference ?? '-' }}</div>
                </div>
                <div class="sub-header-item">
                    <div class="sub-header-label">Communication</div>
                    <div class="sub-header-value" style="font-family: 'DejaVu Sans Mono', monospace;">{{ $invoice->structured_communication }}</div>
                </div>
            </div>

            <!-- Two columns: Company + Client -->
            <div class="two-columns">
                <div class="column-left">
                    <div class="details-box">
                        <div class="details-box-title">Emetteur</div>
                        <div class="address-name">{{ $company->name }}</div>
                        <div class="address-details">
                            @if($company->street){{ $company->street }} {{ $company->house_number }}<br>@endif
                            @if($company->postal_code || $company->city){{ $company->postal_code }} {{ $company->city }}<br>@endif
                            @if($company->email){{ $company->email }}<br>@endif
                            @if($company->phone){{ $company->phone }}@endif
                        </div>
                    </div>
                </div>
                <div class="column-spacer"></div>
                <div class="column-right">
                    <div class="address-block">
                        <div class="address-title">Destinataire</div>
                        <div class="address-name">{{ $invoice->partner->name }}</div>
                        <div class="address-details">
                            @if($invoice->partner->street){{ $invoice->partner->street }} {{ $invoice->partner->house_number }}<br>@endif
                            @if($invoice->partner->postal_code || $invoice->partner->city){{ $invoice->partner->postal_code }} {{ $invoice->partner->city }}<br>@endif
                            @if($invoice->partner->country_code && $invoice->partner->country_code !== 'BE'){{ $invoice->partner->country_code }}<br>@endif
                            @if($invoice->partner->vat_number)TVA: {{ $invoice->partner->vat_number }}@endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Items Table -->
            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width: 45%;">Description</th>
                        <th class="text-center" style="width: 10%;">Qte</th>
                        <th class="text-right" style="width: 15%;">Prix unitaire</th>
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
                                    <div class="item-discount">Remise: {{ number_format($line->discount_percent, 2, ',', ' ') }}%</div>
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

            <!-- Payment Section -->
            <div class="payment-section">
                <div class="payment-details">
                    <div class="payment-title">Coordonnees bancaires</div>
                    <div class="payment-row">
                        <div class="payment-label">Beneficiaire</div>
                        <div class="payment-value">{{ $company->name }}</div>
                    </div>
                    <div class="payment-row">
                        <div class="payment-label">IBAN</div>
                        <div class="payment-value">{{ $company->formatted_iban ?? '-' }}</div>
                    </div>
                    @if($company->default_bic)
                    <div class="payment-row">
                        <div class="payment-label">BIC</div>
                        <div class="payment-value">{{ $company->default_bic }}</div>
                    </div>
                    @endif
                    <div class="payment-row">
                        <div class="payment-label">Communication</div>
                        <div class="payment-value">{{ $invoice->structured_communication }}</div>
                    </div>
                    <div class="payment-row" style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #e2e8f0;">
                        <div class="payment-label" style="font-weight: bold;">Montant du</div>
                        <div class="payment-value" style="font-weight: bold; font-size: 12pt; color: {{ $templateColors['primary'] ?? '#0369a1' }};">{{ number_format($invoice->amount_due, 2, ',', ' ') }} EUR</div>
                    </div>
                </div>
                <div class="payment-spacer"></div>
                @if($company->default_iban && $invoice->amount_due > 0 && isset($qrCode))
                <div class="payment-qr-section">
                    <div class="qr-title">Paiement rapide</div>
                    <div class="qr-code-container">
                        <img src="data:image/png;base64,{{ $qrCode }}" alt="QR Code" style="width: 110px; height: 110px; display: block;">
                    </div>
                    <div class="qr-code-label">Scannez avec votre<br>application bancaire</div>
                </div>
                @endif
            </div>

            <!-- Notes -->
            @if($invoice->notes)
                <div class="notes-section">
                    <div class="notes-title">Remarques</div>
                    <div class="notes-content">{{ $invoice->notes }}</div>
                </div>
            @endif
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="footer-content">
                <div class="footer-left">
                    {{ $company->name }} | TVA: {{ $company->formatted_vat_number ?? '' }} | IBAN: {{ $company->formatted_iban ?? '' }}
                    @if($company->default_bic) | BIC: {{ $company->default_bic }}@endif
                </div>
                <div class="footer-right">
                    @if($company->website){{ $company->website }}@endif
                </div>
            </div>
        </div>
    </div>
</body>
</html>
