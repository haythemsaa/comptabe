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
            color: #1f2937;
            background: white;
        }
        .page { padding: 25px 35px; }

        /* Classic Theme Colors */
        :root {
            --primary: {{ $templateColors['primary'] ?? '#1e3a5f' }};
            --secondary: {{ $templateColors['secondary'] ?? '#4a5568' }};
        }

        /* Header with classic border */
        .header {
            display: table;
            width: 100%;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 3px double {{ $templateColors['primary'] ?? '#1e3a5f' }};
        }
        .header-left {
            display: table-cell;
            width: 55%;
            vertical-align: top;
        }
        .header-right {
            display: table-cell;
            width: 45%;
            vertical-align: top;
            text-align: right;
        }
        .company-logo { max-width: 140px; max-height: 55px; margin-bottom: 8px; }
        .company-name {
            font-size: 16pt;
            font-weight: bold;
            color: {{ $templateColors['primary'] ?? '#1e3a5f' }};
            margin-bottom: 5px;
        }
        .company-info { font-size: 8.5pt; color: #4b5563; line-height: 1.5; }

        .invoice-title {
            font-size: 22pt;
            font-weight: bold;
            color: {{ $templateColors['primary'] ?? '#1e3a5f' }};
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 8px;
        }
        .invoice-number {
            font-size: 11pt;
            color: {{ $templateColors['secondary'] ?? '#4a5568' }};
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 3px;
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 8px;
            border: 1px solid;
        }
        .status-draft { background: #fef3c7; color: #92400e; border-color: #f59e0b; }
        .status-validated { background: #dbeafe; color: #1e40af; border-color: #3b82f6; }
        .status-sent { background: #e0e7ff; color: #3730a3; border-color: #6366f1; }
        .status-paid { background: #d1fae5; color: #065f46; border-color: #10b981; }
        .status-overdue { background: #fee2e2; color: #991b1b; border-color: #ef4444; }

        /* Addresses - Classic boxed style */
        .addresses {
            display: table;
            width: 100%;
            margin-bottom: 25px;
        }
        .address-block {
            display: table-cell;
            width: 48%;
            padding: 15px;
            border: 1px solid #d1d5db;
            background: #fafafa;
        }
        .address-spacer { display: table-cell; width: 4%; }
        .address-title {
            font-size: 8pt;
            text-transform: uppercase;
            color: {{ $templateColors['primary'] ?? '#1e3a5f' }};
            font-weight: bold;
            letter-spacing: 1px;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #e5e7eb;
        }
        .address-name { font-weight: bold; font-size: 10pt; margin-bottom: 5px; }
        .address-details { font-size: 9pt; color: #4b5563; line-height: 1.6; }

        /* Info Grid */
        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 25px;
            border: 1px solid #d1d5db;
        }
        .info-item {
            display: table-cell;
            text-align: center;
            padding: 12px 10px;
            border-right: 1px solid #d1d5db;
            background: #f9fafb;
        }
        .info-item:last-child { border-right: none; }
        .info-label {
            font-size: 7pt;
            text-transform: uppercase;
            color: {{ $templateColors['secondary'] ?? '#4a5568' }};
            margin-bottom: 4px;
            letter-spacing: 0.5px;
        }
        .info-value { font-size: 9pt; font-weight: bold; color: #1f2937; }

        /* Table - Classic style with borders */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .items-table thead th {
            background: {{ $templateColors['primary'] ?? '#1e3a5f' }};
            color: white;
            padding: 10px 12px;
            text-align: left;
            font-size: 8pt;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: bold;
        }
        .items-table thead th:last-child { text-align: right; }
        .items-table tbody td {
            padding: 10px 12px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top;
            font-size: 9pt;
        }
        .items-table tbody tr:nth-child(even) td { background: #fafafa; }
        .items-table .text-right { text-align: right; }
        .items-table .text-center { text-align: center; }
        .item-description { font-weight: 500; }
        .item-discount { font-size: 8pt; color: #6b7280; font-style: italic; }

        /* Totals */
        .totals-section { display: table; width: 100%; margin-bottom: 25px; }
        .totals-spacer { display: table-cell; width: 55%; }
        .totals-table { display: table-cell; width: 45%; }
        .totals-row { display: table; width: 100%; border: 1px solid #e5e7eb; border-top: none; }
        .totals-row:first-child { border-top: 1px solid #e5e7eb; }
        .totals-label {
            display: table-cell;
            width: 55%;
            padding: 8px 12px;
            text-align: right;
            color: {{ $templateColors['secondary'] ?? '#4a5568' }};
            font-size: 9pt;
            background: #f9fafb;
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
            background: {{ $templateColors['primary'] ?? '#1e3a5f' }};
            color: white;
        }
        .totals-total .totals-label {
            background: {{ $templateColors['primary'] ?? '#1e3a5f' }};
            color: rgba(255,255,255,0.8);
            font-size: 10pt;
        }
        .totals-total .totals-value {
            font-size: 12pt;
            font-weight: bold;
        }

        /* Payment Info */
        .payment-section {
            border: 1px solid #d1d5db;
            padding: 18px;
            margin-bottom: 25px;
        }
        .payment-title {
            font-size: 10pt;
            font-weight: bold;
            margin-bottom: 15px;
            color: {{ $templateColors['primary'] ?? '#1e3a5f' }};
            text-transform: uppercase;
            letter-spacing: 1px;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
        }
        .payment-container { display: table; width: 100%; }
        .payment-grid { display: table-cell; width: 65%; vertical-align: top; }
        .payment-item { margin-bottom: 10px; }
        .payment-label {
            font-size: 7pt;
            text-transform: uppercase;
            color: {{ $templateColors['secondary'] ?? '#4a5568' }};
            margin-bottom: 2px;
        }
        .payment-value { font-family: 'DejaVu Sans Mono', monospace; font-size: 9pt; color: #1f2937; }
        .payment-qr { display: table-cell; width: 35%; text-align: right; vertical-align: top; }
        .qr-code-container {
            display: inline-block;
            padding: 8px;
            background: white;
            border: 1px solid #d1d5db;
        }
        .qr-code-label { font-size: 7pt; text-align: center; color: #6b7280; margin-top: 5px; }

        /* Notes */
        .notes-section { margin-bottom: 25px; }
        .notes-title {
            font-size: 8pt;
            text-transform: uppercase;
            color: {{ $templateColors['secondary'] ?? '#4a5568' }};
            margin-bottom: 8px;
            font-weight: bold;
        }
        .notes-content { font-size: 9pt; color: #4b5563; white-space: pre-line; }

        /* Footer */
        .footer {
            position: fixed;
            bottom: 20px;
            left: 35px;
            right: 35px;
            text-align: center;
            font-size: 7pt;
            color: #9ca3af;
            border-top: 2px solid {{ $templateColors['primary'] ?? '#1e3a5f' }};
            padding-top: 12px;
        }
    </style>
</head>
<body>
    <div class="page">
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
                <div class="invoice-title">Facture</div>
                <div class="invoice-number">N° {{ $invoice->invoice_number }}</div>
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
                        <div class="payment-value" style="font-weight: bold; font-size: 11pt;">{{ number_format($invoice->amount_due, 2, ',', ' ') }} EUR</div>
                    </div>
                </div>
                @if($company->default_iban && $invoice->amount_due > 0 && isset($qrCode))
                <div class="payment-qr">
                    <div class="qr-code-container">
                        <img src="data:image/png;base64,{{ $qrCode }}" alt="QR Code Paiement" style="width: 120px; height: 120px; display: block;">
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

        <!-- Footer -->
        <div class="footer">
            {{ $company->name }} | TVA: {{ $company->formatted_vat_number ?? '' }} | IBAN: {{ $company->formatted_iban ?? '' }}
            @if($company->default_bic) | BIC: {{ $company->default_bic }}@endif
            <br>
            @if($company->website){{ $company->website }} | @endif
            @if($company->email){{ $company->email }} | @endif
            @if($company->phone){{ $company->phone }}@endif
        </div>
    </div>
</body>
</html>
