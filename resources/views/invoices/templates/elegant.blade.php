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
            line-height: 1.5;
            color: {{ $templateColors['secondary'] ?? '#292524' }};
            background: white;
        }
        .page {
            padding: 40px 50px;
            border: 1px solid #e7e5e4;
            margin: 15px;
        }

        /* Elegant decorative elements */
        .elegant-line {
            height: 1px;
            background: linear-gradient(90deg, transparent, {{ $templateColors['primary'] ?? '#78716c' }}, transparent);
            margin: 25px 0;
        }
        .elegant-line-thick {
            height: 2px;
            background: linear-gradient(90deg, transparent, {{ $templateColors['primary'] ?? '#78716c' }}, transparent);
            margin: 30px 0;
        }

        /* Header */
        .header {
            text-align: center;
            margin-bottom: 10px;
        }
        .company-logo { max-width: 120px; max-height: 50px; margin-bottom: 15px; }
        .company-name {
            font-size: 20pt;
            font-weight: 300;
            color: {{ $templateColors['secondary'] ?? '#292524' }};
            letter-spacing: 4px;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .company-info {
            font-size: 8pt;
            color: {{ $templateColors['primary'] ?? '#78716c' }};
            letter-spacing: 1px;
        }

        /* Invoice title section */
        .invoice-section {
            text-align: center;
            margin-bottom: 10px;
        }
        .invoice-title {
            font-size: 10pt;
            text-transform: uppercase;
            letter-spacing: 6px;
            color: {{ $templateColors['primary'] ?? '#78716c' }};
            margin-bottom: 8px;
        }
        .invoice-number {
            font-size: 16pt;
            font-weight: 300;
            color: {{ $templateColors['secondary'] ?? '#292524' }};
            letter-spacing: 2px;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 15px;
            font-size: 7pt;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-top: 10px;
            border: 1px solid;
        }
        .status-draft { color: #92400e; border-color: #fbbf24; }
        .status-validated { color: #1e40af; border-color: #3b82f6; }
        .status-sent { color: #3730a3; border-color: #6366f1; }
        .status-paid { color: #065f46; border-color: #10b981; }
        .status-overdue { color: #991b1b; border-color: #ef4444; }

        /* Info row */
        .info-row {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }
        .info-item {
            display: table-cell;
            text-align: center;
            padding: 0 15px;
        }
        .info-label {
            font-size: 7pt;
            text-transform: uppercase;
            color: {{ $templateColors['primary'] ?? '#78716c' }};
            letter-spacing: 2px;
            margin-bottom: 5px;
        }
        .info-value {
            font-size: 10pt;
            color: {{ $templateColors['secondary'] ?? '#292524' }};
        }

        /* Addresses - Elegant centered */
        .addresses {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }
        .address-block {
            display: table-cell;
            width: 48%;
            padding: 20px;
            text-align: center;
            border: 1px solid #e7e5e4;
        }
        .address-spacer { display: table-cell; width: 4%; }
        .address-title {
            font-size: 7pt;
            text-transform: uppercase;
            color: {{ $templateColors['primary'] ?? '#78716c' }};
            letter-spacing: 3px;
            margin-bottom: 12px;
        }
        .address-name {
            font-weight: 500;
            font-size: 11pt;
            margin-bottom: 8px;
            color: {{ $templateColors['secondary'] ?? '#292524' }};
        }
        .address-details {
            font-size: 9pt;
            color: {{ $templateColors['primary'] ?? '#78716c' }};
            line-height: 1.7;
        }

        /* Table - Elegant minimal */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .items-table thead th {
            padding: 12px 10px;
            text-align: left;
            font-size: 7pt;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: {{ $templateColors['primary'] ?? '#78716c' }};
            border-top: 1px solid {{ $templateColors['primary'] ?? '#78716c' }};
            border-bottom: 1px solid {{ $templateColors['primary'] ?? '#78716c' }};
        }
        .items-table thead th:last-child { text-align: right; }
        .items-table tbody td {
            padding: 15px 10px;
            border-bottom: 1px solid #f5f5f4;
            vertical-align: top;
            font-size: 9pt;
        }
        .items-table tbody tr:last-child td {
            border-bottom: 1px solid #e7e5e4;
        }
        .items-table .text-right { text-align: right; }
        .items-table .text-center { text-align: center; }
        .item-description { font-weight: 400; color: {{ $templateColors['secondary'] ?? '#292524' }}; }
        .item-discount { font-size: 8pt; color: {{ $templateColors['primary'] ?? '#78716c' }}; font-style: italic; margin-top: 3px; }

        /* Totals - Elegant */
        .totals-section { display: table; width: 100%; margin-bottom: 30px; }
        .totals-spacer { display: table-cell; width: 55%; }
        .totals-table { display: table-cell; width: 45%; }
        .totals-row {
            display: table;
            width: 100%;
            margin-bottom: 5px;
        }
        .totals-label {
            display: table-cell;
            width: 55%;
            padding: 6px 10px;
            text-align: right;
            color: {{ $templateColors['primary'] ?? '#78716c' }};
            font-size: 9pt;
        }
        .totals-value {
            display: table-cell;
            width: 45%;
            padding: 6px 10px;
            text-align: right;
            font-size: 9pt;
        }
        .totals-total {
            border-top: 1px solid {{ $templateColors['primary'] ?? '#78716c' }};
            border-bottom: 1px solid {{ $templateColors['primary'] ?? '#78716c' }};
            margin-top: 10px;
            padding: 8px 0;
        }
        .totals-total .totals-label {
            color: {{ $templateColors['secondary'] ?? '#292524' }};
            font-size: 10pt;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .totals-total .totals-value {
            font-size: 14pt;
            font-weight: 300;
            letter-spacing: 1px;
        }

        /* Payment Info - Elegant */
        .payment-section {
            text-align: center;
            margin-bottom: 30px;
            padding: 25px;
            border: 1px solid #e7e5e4;
        }
        .payment-title {
            font-size: 7pt;
            text-transform: uppercase;
            letter-spacing: 3px;
            color: {{ $templateColors['primary'] ?? '#78716c' }};
            margin-bottom: 20px;
        }
        .payment-grid {
            display: table;
            width: 100%;
        }
        .payment-details {
            display: table-cell;
            width: 60%;
            text-align: left;
            vertical-align: top;
        }
        .payment-qr {
            display: table-cell;
            width: 40%;
            text-align: center;
            vertical-align: top;
        }
        .payment-row {
            margin-bottom: 10px;
        }
        .payment-label {
            font-size: 7pt;
            text-transform: uppercase;
            color: {{ $templateColors['primary'] ?? '#78716c' }};
            letter-spacing: 1px;
            margin-bottom: 3px;
        }
        .payment-value {
            font-family: 'DejaVu Sans Mono', monospace;
            font-size: 9pt;
            color: {{ $templateColors['secondary'] ?? '#292524' }};
        }
        .payment-amount {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #e7e5e4;
        }
        .payment-amount .payment-value {
            font-size: 16pt;
            font-weight: 300;
            letter-spacing: 1px;
        }
        .qr-code-container {
            display: inline-block;
            padding: 10px;
            border: 1px solid #e7e5e4;
        }
        .qr-code-label {
            font-size: 7pt;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: {{ $templateColors['primary'] ?? '#78716c' }};
            margin-top: 10px;
        }

        /* Notes */
        .notes-section {
            text-align: center;
            margin-bottom: 30px;
        }
        .notes-title {
            font-size: 7pt;
            text-transform: uppercase;
            letter-spacing: 3px;
            color: {{ $templateColors['primary'] ?? '#78716c' }};
            margin-bottom: 10px;
        }
        .notes-content {
            font-size: 9pt;
            color: {{ $templateColors['primary'] ?? '#78716c' }};
            white-space: pre-line;
            font-style: italic;
        }

        /* Footer */
        .footer {
            text-align: center;
            font-size: 7pt;
            color: {{ $templateColors['primary'] ?? '#78716c' }};
            letter-spacing: 1px;
            padding-top: 20px;
        }
        .footer-separator {
            width: 50px;
            height: 1px;
            background: {{ $templateColors['primary'] ?? '#78716c' }};
            margin: 0 auto 15px;
        }
    </style>
</head>
<body>
    <div class="page">
        <!-- Header -->
        <div class="header">
            @if($company->logo_path)
                <img src="{{ storage_path('app/public/' . $company->logo_path) }}" alt="{{ $company->name }}" class="company-logo">
            @endif
            <div class="company-name">{{ $company->name }}</div>
            <div class="company-info">
                @if($company->street){{ $company->street }} {{ $company->house_number }} | @endif
                @if($company->postal_code || $company->city){{ $company->postal_code }} {{ $company->city }}@endif
                @if($company->vat_number) | {{ $company->formatted_vat_number }}@endif
            </div>
        </div>

        <div class="elegant-line-thick"></div>

        <!-- Invoice Title -->
        <div class="invoice-section">
            <div class="invoice-title">Facture</div>
            <div class="invoice-number">{{ $invoice->invoice_number }}</div>
            <span class="status-badge status-{{ $invoice->status }}">{{ $invoice->status_label }}</span>
        </div>

        <div class="elegant-line"></div>

        <!-- Info Row -->
        <div class="info-row">
            <div class="info-item">
                <div class="info-label">Date</div>
                <div class="info-value">{{ $invoice->invoice_date->format('d F Y') }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Echeance</div>
                <div class="info-value">{{ $invoice->due_date ? $invoice->due_date->format('d F Y') : '-' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Reference</div>
                <div class="info-value">{{ $invoice->reference ?? '-' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Communication</div>
                <div class="info-value" style="font-family: 'DejaVu Sans Mono', monospace; font-size: 9pt;">{{ $invoice->structured_communication }}</div>
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
                    @if($invoice->partner->vat_number){{ $invoice->partner->vat_number }}@endif
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

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 45%;">Description</th>
                    <th class="text-center" style="width: 12%;">Quantite</th>
                    <th class="text-right" style="width: 15%;">Prix</th>
                    <th class="text-center" style="width: 10%;">TVA</th>
                    <th class="text-right" style="width: 18%;">Montant</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->lines as $line)
                    <tr>
                        <td>
                            <div class="item-description">{{ $line->description }}</div>
                            @if($line->discount_percent > 0)
                                <div class="item-discount">Remise de {{ number_format($line->discount_percent, 0) }}% appliquee</div>
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
                    <div class="totals-label">Sous-total</div>
                    <div class="totals-value">{{ number_format($invoice->total_excl_vat, 2, ',', ' ') }} EUR</div>
                </div>
                @foreach($invoice->vatSummary() as $rate => $amount)
                    <div class="totals-row">
                        <div class="totals-label">TVA {{ $rate }}%</div>
                        <div class="totals-value">{{ number_format($amount, 2, ',', ' ') }} EUR</div>
                    </div>
                @endforeach
                <div class="totals-row totals-total">
                    <div class="totals-label">Total</div>
                    <div class="totals-value">{{ number_format($invoice->total_incl_vat, 2, ',', ' ') }} EUR</div>
                </div>
            </div>
        </div>

        <div class="elegant-line"></div>

        <!-- Payment Info -->
        <div class="payment-section">
            <div class="payment-title">Informations de paiement</div>
            <div class="payment-grid">
                <div class="payment-details">
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
                    <div class="payment-row payment-amount">
                        <div class="payment-label">Montant a regler</div>
                        <div class="payment-value">{{ number_format($invoice->amount_due, 2, ',', ' ') }} EUR</div>
                    </div>
                </div>
                @if($company->default_iban && $invoice->amount_due > 0 && isset($qrCode))
                <div class="payment-qr">
                    <div class="qr-code-container">
                        <img src="data:image/png;base64,{{ $qrCode }}" alt="QR Code" style="width: 100px; height: 100px; display: block;">
                    </div>
                    <div class="qr-code-label">Paiement instantane</div>
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
            <div class="footer-separator"></div>
            {{ $company->name }}
            @if($company->email) | {{ $company->email }}@endif
            @if($company->phone) | {{ $company->phone }}@endif
            @if($company->website)<br>{{ $company->website }}@endif
        </div>
    </div>
</body>
</html>
