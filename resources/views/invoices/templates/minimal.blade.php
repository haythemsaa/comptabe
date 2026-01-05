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
            line-height: 1.6;
            color: {{ $templateColors['primary'] ?? '#0f172a' }};
            background: white;
        }
        .page { padding: 50px 60px; }

        /* Header - Minimal */
        .header {
            display: table;
            width: 100%;
            margin-bottom: 50px;
        }
        .header-left {
            display: table-cell;
            width: 50%;
            vertical-align: bottom;
        }
        .header-right {
            display: table-cell;
            width: 50%;
            vertical-align: bottom;
            text-align: right;
        }
        .company-logo { max-width: 120px; max-height: 50px; margin-bottom: 15px; opacity: 0.9; }
        .company-name {
            font-size: 14pt;
            font-weight: 300;
            color: {{ $templateColors['primary'] ?? '#0f172a' }};
            letter-spacing: 1px;
        }
        .company-info { font-size: 8pt; color: {{ $templateColors['secondary'] ?? '#64748b' }}; line-height: 1.8; margin-top: 8px; }

        .invoice-title {
            font-size: 10pt;
            font-weight: 300;
            color: {{ $templateColors['secondary'] ?? '#64748b' }};
            text-transform: uppercase;
            letter-spacing: 4px;
        }
        .invoice-number {
            font-size: 24pt;
            font-weight: 300;
            color: {{ $templateColors['primary'] ?? '#0f172a' }};
            margin-top: 5px;
        }
        .status-badge {
            display: inline-block;
            padding: 3px 12px;
            font-size: 7pt;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 10px;
            border: 1px solid;
        }
        .status-draft { color: #92400e; border-color: #fbbf24; }
        .status-validated { color: #1e40af; border-color: #3b82f6; }
        .status-sent { color: #3730a3; border-color: #6366f1; }
        .status-paid { color: #065f46; border-color: #10b981; }
        .status-overdue { color: #991b1b; border-color: #ef4444; }

        /* Divider */
        .divider {
            height: 1px;
            background: #e2e8f0;
            margin: 40px 0;
        }

        /* Addresses - Minimal layout */
        .addresses {
            display: table;
            width: 100%;
            margin-bottom: 40px;
        }
        .address-block {
            display: table-cell;
            width: 48%;
            vertical-align: top;
        }
        .address-spacer { display: table-cell; width: 4%; }
        .address-title {
            font-size: 7pt;
            text-transform: uppercase;
            color: {{ $templateColors['secondary'] ?? '#64748b' }};
            letter-spacing: 2px;
            margin-bottom: 12px;
        }
        .address-name {
            font-weight: 500;
            font-size: 11pt;
            margin-bottom: 8px;
            color: {{ $templateColors['primary'] ?? '#0f172a' }};
        }
        .address-details { font-size: 9pt; color: {{ $templateColors['secondary'] ?? '#64748b' }}; line-height: 1.8; }

        /* Info Grid - Minimal */
        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 40px;
        }
        .info-item {
            display: table-cell;
            padding-right: 30px;
        }
        .info-item:last-child { padding-right: 0; }
        .info-label {
            font-size: 7pt;
            text-transform: uppercase;
            color: {{ $templateColors['secondary'] ?? '#64748b' }};
            letter-spacing: 1px;
            margin-bottom: 5px;
        }
        .info-value { font-size: 10pt; color: {{ $templateColors['primary'] ?? '#0f172a' }}; }

        /* Table - Minimal borders */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
        }
        .items-table thead th {
            padding: 15px 0;
            text-align: left;
            font-size: 7pt;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: {{ $templateColors['secondary'] ?? '#64748b' }};
            border-bottom: 1px solid {{ $templateColors['primary'] ?? '#0f172a' }};
            font-weight: 500;
        }
        .items-table thead th:last-child { text-align: right; }
        .items-table tbody td {
            padding: 18px 0;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: top;
            font-size: 9pt;
        }
        .items-table tbody tr:last-child td { border-bottom: 1px solid #e2e8f0; }
        .items-table .text-right { text-align: right; }
        .items-table .text-center { text-align: center; }
        .item-description { font-weight: 400; color: {{ $templateColors['primary'] ?? '#0f172a' }}; }
        .item-discount { font-size: 8pt; color: {{ $templateColors['secondary'] ?? '#64748b' }}; margin-top: 4px; }

        /* Totals - Minimal */
        .totals-section { display: table; width: 100%; margin-bottom: 50px; }
        .totals-spacer { display: table-cell; width: 60%; }
        .totals-table { display: table-cell; width: 40%; }
        .totals-row {
            display: table;
            width: 100%;
            margin-bottom: 8px;
        }
        .totals-label {
            display: table-cell;
            width: 50%;
            padding: 5px 0;
            text-align: right;
            color: {{ $templateColors['secondary'] ?? '#64748b' }};
            font-size: 9pt;
            padding-right: 20px;
        }
        .totals-value {
            display: table-cell;
            width: 50%;
            padding: 5px 0;
            text-align: right;
            font-size: 9pt;
        }
        .totals-total {
            border-top: 1px solid {{ $templateColors['primary'] ?? '#0f172a' }};
            margin-top: 15px;
            padding-top: 15px;
        }
        .totals-total .totals-label {
            color: {{ $templateColors['primary'] ?? '#0f172a' }};
            font-size: 10pt;
        }
        .totals-total .totals-value {
            font-size: 16pt;
            font-weight: 300;
        }

        /* Payment Info - Minimal */
        .payment-section {
            margin-bottom: 40px;
            padding-top: 30px;
            border-top: 1px solid #e2e8f0;
        }
        .payment-title {
            font-size: 7pt;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 20px;
            color: {{ $templateColors['secondary'] ?? '#64748b' }};
        }
        .payment-container { display: table; width: 100%; }
        .payment-grid { display: table-cell; width: 65%; vertical-align: top; }
        .payment-row { display: table; width: 100%; margin-bottom: 10px; }
        .payment-label {
            display: table-cell;
            width: 30%;
            font-size: 8pt;
            color: {{ $templateColors['secondary'] ?? '#64748b' }};
            padding-right: 15px;
        }
        .payment-value {
            display: table-cell;
            font-family: 'DejaVu Sans Mono', monospace;
            font-size: 9pt;
            color: {{ $templateColors['primary'] ?? '#0f172a' }};
        }
        .payment-qr { display: table-cell; width: 35%; text-align: right; vertical-align: top; }
        .qr-code-container {
            display: inline-block;
            padding: 10px;
            border: 1px solid #e2e8f0;
        }
        .qr-code-label { font-size: 7pt; text-align: center; color: {{ $templateColors['secondary'] ?? '#64748b' }}; margin-top: 8px; letter-spacing: 1px; }

        /* Notes */
        .notes-section { margin-bottom: 40px; }
        .notes-title {
            font-size: 7pt;
            text-transform: uppercase;
            color: {{ $templateColors['secondary'] ?? '#64748b' }};
            letter-spacing: 2px;
            margin-bottom: 10px;
        }
        .notes-content { font-size: 9pt; color: {{ $templateColors['secondary'] ?? '#64748b' }}; white-space: pre-line; }

        /* Footer */
        .footer {
            position: fixed;
            bottom: 40px;
            left: 60px;
            right: 60px;
            text-align: center;
            font-size: 7pt;
            color: {{ $templateColors['secondary'] ?? '#64748b' }};
            letter-spacing: 1px;
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
                    @if($company->vat_number){{ $company->formatted_vat_number }}@endif
                </div>
            </div>
            <div class="header-right">
                <div class="invoice-title">Facture</div>
                <div class="invoice-number">{{ $invoice->invoice_number }}</div>
                <span class="status-badge status-{{ $invoice->status }}">{{ $invoice->status_label }}</span>
            </div>
        </div>

        <div class="divider"></div>

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

        <!-- Info Grid -->
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">Date</div>
                <div class="info-value">{{ $invoice->invoice_date->format('d.m.Y') }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Echeance</div>
                <div class="info-value">{{ $invoice->due_date ? $invoice->due_date->format('d.m.Y') : '-' }}</div>
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
                                <div class="item-discount">Remise {{ number_format($line->discount_percent, 0) }}%</div>
                            @endif
                        </td>
                        <td class="text-center">{{ number_format($line->quantity, 2, ',', ' ') }}</td>
                        <td class="text-right">{{ number_format($line->unit_price, 2, ',', ' ') }}</td>
                        <td class="text-center">{{ number_format($line->vat_rate, 0) }}%</td>
                        <td class="text-right">{{ number_format($line->total_excl_vat, 2, ',', ' ') }}</td>
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

        <!-- Payment Info -->
        <div class="payment-section">
            <div class="payment-title">Paiement</div>
            <div class="payment-container">
                <div class="payment-grid">
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
                    <div class="payment-row" style="margin-top: 15px;">
                        <div class="payment-label">A payer</div>
                        <div class="payment-value" style="font-size: 14pt; font-weight: 500;">{{ number_format($invoice->amount_due, 2, ',', ' ') }} EUR</div>
                    </div>
                </div>
                @if($company->default_iban && $invoice->amount_due > 0 && isset($qrCode))
                <div class="payment-qr">
                    <div class="qr-code-container">
                        <img src="data:image/png;base64,{{ $qrCode }}" alt="QR Code" style="width: 100px; height: 100px; display: block;">
                    </div>
                    <div class="qr-code-label">Scanner pour payer</div>
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
            {{ $company->name }} - {{ $company->formatted_vat_number ?? '' }} - {{ $company->formatted_iban ?? '' }}
        </div>
    </div>
</body>
</html>
