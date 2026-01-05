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
        .page { padding: 0; position: relative; }

        /* Creative diagonal header */
        .header-bg {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 180px;
            background: {{ $templateColors['primary'] ?? '#f59e0b' }};
            clip-path: polygon(0 0, 100% 0, 100% 70%, 0 100%);
        }
        .header-overlay {
            position: absolute;
            top: 0;
            right: 0;
            width: 40%;
            height: 180px;
            background: {{ $templateColors['secondary'] ?? '#1f2937' }};
            clip-path: polygon(30% 0, 100% 0, 100% 100%, 0 100%);
        }

        .content { position: relative; padding: 30px 40px; }

        /* Header */
        .header {
            display: table;
            width: 100%;
            margin-bottom: 30px;
            padding-top: 10px;
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
        .company-logo { max-width: 130px; max-height: 50px; margin-bottom: 10px; }
        .company-name {
            font-size: 16pt;
            font-weight: bold;
            color: white;
            margin-bottom: 5px;
        }
        .company-info { font-size: 8pt; color: rgba(255,255,255,0.85); line-height: 1.6; }

        .invoice-badge {
            display: inline-block;
            background: white;
            color: {{ $templateColors['secondary'] ?? '#1f2937' }};
            padding: 10px 25px;
            font-size: 16pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 3px;
            margin-bottom: 8px;
            transform: skewX(-5deg);
        }
        .invoice-number {
            font-size: 11pt;
            color: white;
            font-weight: 500;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 10px;
            background: white;
            transform: skewX(-5deg);
        }
        .status-draft { color: #92400e; }
        .status-validated { color: #1e40af; }
        .status-sent { color: #3730a3; }
        .status-paid { color: #065f46; }
        .status-overdue { color: #991b1b; }

        /* Main content area */
        .main-content { margin-top: 100px; }

        /* Addresses - Creative cards */
        .addresses {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }
        .address-block {
            display: table-cell;
            width: 48%;
            padding: 18px;
            background: #f9fafb;
            position: relative;
        }
        .address-block::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: {{ $templateColors['primary'] ?? '#f59e0b' }};
        }
        .address-spacer { display: table-cell; width: 4%; }
        .address-title {
            font-size: 8pt;
            text-transform: uppercase;
            color: {{ $templateColors['primary'] ?? '#f59e0b' }};
            font-weight: bold;
            letter-spacing: 2px;
            margin-bottom: 10px;
        }
        .address-name {
            font-weight: bold;
            font-size: 11pt;
            margin-bottom: 5px;
            color: {{ $templateColors['secondary'] ?? '#1f2937' }};
        }
        .address-details { font-size: 9pt; color: #6b7280; line-height: 1.6; }

        /* Info Grid - Creative style */
        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }
        .info-item {
            display: table-cell;
            text-align: center;
            padding: 15px;
            background: {{ $templateColors['secondary'] ?? '#1f2937' }};
            position: relative;
        }
        .info-item::after {
            content: '';
            position: absolute;
            right: 0;
            top: 20%;
            height: 60%;
            width: 1px;
            background: rgba(255,255,255,0.2);
        }
        .info-item:last-child::after { display: none; }
        .info-label {
            font-size: 7pt;
            text-transform: uppercase;
            color: {{ $templateColors['primary'] ?? '#f59e0b' }};
            margin-bottom: 5px;
            letter-spacing: 1px;
            font-weight: 600;
        }
        .info-value { font-size: 10pt; font-weight: bold; color: white; }

        /* Table - Creative style */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .items-table thead th {
            background: {{ $templateColors['primary'] ?? '#f59e0b' }};
            color: {{ $templateColors['secondary'] ?? '#1f2937' }};
            padding: 12px 15px;
            text-align: left;
            font-size: 8pt;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: bold;
        }
        .items-table thead th:last-child { text-align: right; }
        .items-table tbody td {
            padding: 15px;
            border-bottom: 2px solid #f3f4f6;
            vertical-align: top;
            font-size: 9pt;
        }
        .items-table tbody tr:nth-child(odd) td { background: #fafafa; }
        .items-table .text-right { text-align: right; }
        .items-table .text-center { text-align: center; }
        .item-description { font-weight: 600; color: {{ $templateColors['secondary'] ?? '#1f2937' }}; }
        .item-discount {
            display: inline-block;
            font-size: 7pt;
            color: white;
            background: {{ $templateColors['primary'] ?? '#f59e0b' }};
            padding: 2px 8px;
            margin-top: 4px;
        }

        /* Totals - Creative */
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
            padding: 8px 12px;
            text-align: right;
            color: #6b7280;
            font-size: 9pt;
        }
        .totals-value {
            display: table-cell;
            width: 45%;
            padding: 8px 12px;
            text-align: right;
            font-weight: 600;
            font-size: 9pt;
        }
        .totals-total {
            background: {{ $templateColors['secondary'] ?? '#1f2937' }};
            color: white;
            margin-top: 10px;
            transform: skewX(-3deg);
        }
        .totals-total .totals-label {
            color: {{ $templateColors['primary'] ?? '#f59e0b' }};
            font-size: 11pt;
        }
        .totals-total .totals-value {
            font-size: 14pt;
            font-weight: bold;
        }

        /* Payment Info - Creative */
        .payment-section {
            background: linear-gradient(135deg, {{ $templateColors['primary'] ?? '#f59e0b' }}10, {{ $templateColors['primary'] ?? '#f59e0b' }}05);
            padding: 20px;
            margin-bottom: 30px;
            border-left: 4px solid {{ $templateColors['primary'] ?? '#f59e0b' }};
        }
        .payment-title {
            font-size: 11pt;
            font-weight: bold;
            margin-bottom: 15px;
            color: {{ $templateColors['secondary'] ?? '#1f2937' }};
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .payment-container { display: table; width: 100%; }
        .payment-grid { display: table-cell; width: 60%; vertical-align: top; }
        .payment-item { margin-bottom: 12px; }
        .payment-label {
            font-size: 7pt;
            text-transform: uppercase;
            color: {{ $templateColors['primary'] ?? '#f59e0b' }};
            margin-bottom: 3px;
            font-weight: bold;
            letter-spacing: 1px;
        }
        .payment-value { font-family: 'DejaVu Sans Mono', monospace; font-size: 9pt; color: {{ $templateColors['secondary'] ?? '#1f2937' }}; }
        .payment-qr { display: table-cell; width: 40%; text-align: right; vertical-align: top; }
        .qr-code-container {
            display: inline-block;
            padding: 12px;
            background: white;
            border: 3px solid {{ $templateColors['primary'] ?? '#f59e0b' }};
            transform: rotate(2deg);
        }
        .qr-code-label {
            font-size: 8pt;
            text-align: center;
            color: {{ $templateColors['secondary'] ?? '#1f2937' }};
            margin-top: 8px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Notes */
        .notes-section { margin-bottom: 30px; }
        .notes-title {
            font-size: 9pt;
            text-transform: uppercase;
            color: {{ $templateColors['primary'] ?? '#f59e0b' }};
            margin-bottom: 8px;
            font-weight: bold;
            letter-spacing: 1px;
        }
        .notes-content { font-size: 9pt; color: #6b7280; white-space: pre-line; }

        /* Footer */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8pt;
            color: white;
            background: {{ $templateColors['secondary'] ?? '#1f2937' }};
            padding: 15px 40px;
        }
        .footer-accent {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 30%;
            height: 100%;
            background: {{ $templateColors['primary'] ?? '#f59e0b' }};
            clip-path: polygon(0 0, 70% 0, 100% 100%, 0 100%);
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="header-bg"></div>
        <div class="header-overlay"></div>

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
                        @if($company->vat_number)TVA: {{ $company->formatted_vat_number }}@endif
                    </div>
                </div>
                <div class="header-right">
                    <span class="invoice-badge">Facture</span>
                    <div class="invoice-number">N{{ $invoice->invoice_number }}</div>
                    <span class="status-badge status-{{ $invoice->status }}">{{ $invoice->status_label }}</span>
                </div>
            </div>

            <div class="main-content">
                <!-- Addresses -->
                <div class="addresses">
                    <div class="address-block">
                        <div class="address-title">Facturer a</div>
                        <div class="address-name">{{ $invoice->partner->name }}</div>
                        <div class="address-details">
                            @if($invoice->partner->street){{ $invoice->partner->street }} {{ $invoice->partner->house_number }}<br>@endif
                            @if($invoice->partner->postal_code || $invoice->partner->city){{ $invoice->partner->postal_code }} {{ $invoice->partner->city }}<br>@endif
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
                        <div class="info-label">Date facture</div>
                        <div class="info-value">{{ $invoice->invoice_date->format('d/m/Y') }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Echeance</div>
                        <div class="info-value">{{ $invoice->due_date ? $invoice->due_date->format('d/m/Y') : '-' }}</div>
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
                                        <span class="item-discount">-{{ number_format($line->discount_percent, 0) }}%</span>
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
                    <div class="payment-title">Paiement</div>
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
                                <div class="payment-label">Communication</div>
                                <div class="payment-value">{{ $invoice->structured_communication }}</div>
                            </div>
                            <div class="payment-item">
                                <div class="payment-label">A payer</div>
                                <div class="payment-value" style="font-weight: bold; font-size: 14pt; color: {{ $templateColors['primary'] ?? '#f59e0b' }};">{{ number_format($invoice->amount_due, 2, ',', ' ') }} EUR</div>
                            </div>
                        </div>
                        @if($company->default_iban && $invoice->amount_due > 0 && isset($qrCode))
                        <div class="payment-qr">
                            <div class="qr-code-container">
                                <img src="data:image/png;base64,{{ $qrCode }}" alt="QR Code" style="width: 120px; height: 120px; display: block;">
                            </div>
                            <div class="qr-code-label">Scannez!</div>
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
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="footer-accent"></div>
            <span style="position: relative;">
                {{ $company->name }} | TVA: {{ $company->formatted_vat_number ?? '' }} | IBAN: {{ $company->formatted_iban ?? '' }}
            </span>
        </div>
    </div>
</body>
</html>
