<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Facture {{ $invoice->invoice_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10pt;
            line-height: 1.4;
            color: #1f2937;
        }
        .page {
            padding: 30px 40px;
        }

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
        .company-name {
            font-size: 18pt;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 5px;
        }
        .company-info {
            font-size: 9pt;
            color: #6b7280;
        }
        .invoice-title {
            font-size: 24pt;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 10px;
        }
        .invoice-number {
            font-size: 12pt;
            color: #6b7280;
        }

        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 9pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 10px;
        }
        .status-draft { background: #fef3c7; color: #92400e; }
        .status-validated { background: #dbeafe; color: #1e40af; }
        .status-sent { background: #e0e7ff; color: #3730a3; }
        .status-paid { background: #d1fae5; color: #065f46; }
        .status-overdue { background: #fee2e2; color: #991b1b; }

        /* Addresses */
        .addresses {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }
        .address-block {
            display: table-cell;
            width: 48%;
            padding: 15px;
            background: #f9fafb;
            border-radius: 8px;
        }
        .address-block:first-child {
            margin-right: 4%;
        }
        .address-title {
            font-size: 8pt;
            text-transform: uppercase;
            color: #6b7280;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }
        .address-name {
            font-weight: bold;
            font-size: 11pt;
            margin-bottom: 5px;
        }
        .address-details {
            font-size: 9pt;
            color: #4b5563;
        }

        /* Info Grid */
        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 30px;
            background: #eff6ff;
            border-radius: 8px;
            padding: 15px;
        }
        .info-item {
            display: table-cell;
            text-align: center;
            padding: 0 10px;
            border-right: 1px solid #bfdbfe;
        }
        .info-item:last-child {
            border-right: none;
        }
        .info-label {
            font-size: 8pt;
            text-transform: uppercase;
            color: #6b7280;
            margin-bottom: 5px;
        }
        .info-value {
            font-size: 10pt;
            font-weight: bold;
            color: #1f2937;
        }

        /* Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .items-table thead th {
            background: #1f2937;
            color: white;
            padding: 10px 12px;
            text-align: left;
            font-size: 9pt;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .items-table thead th:first-child {
            border-radius: 8px 0 0 0;
        }
        .items-table thead th:last-child {
            border-radius: 0 8px 0 0;
            text-align: right;
        }
        .items-table tbody td {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top;
        }
        .items-table tbody tr:last-child td {
            border-bottom: none;
        }
        .items-table .text-right {
            text-align: right;
        }
        .items-table .text-center {
            text-align: center;
        }
        .item-description {
            font-weight: 500;
        }
        .item-discount {
            font-size: 8pt;
            color: #6b7280;
        }

        /* Totals */
        .totals-section {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }
        .totals-spacer {
            display: table-cell;
            width: 60%;
        }
        .totals-table {
            display: table-cell;
            width: 40%;
        }
        .totals-row {
            display: table;
            width: 100%;
            margin-bottom: 5px;
        }
        .totals-label {
            display: table-cell;
            width: 60%;
            padding: 5px 10px;
            text-align: right;
            color: #6b7280;
        }
        .totals-value {
            display: table-cell;
            width: 40%;
            padding: 5px 10px;
            text-align: right;
            font-weight: 500;
        }
        .totals-total {
            background: #1f2937;
            color: white;
            border-radius: 8px;
            margin-top: 10px;
        }
        .totals-total .totals-label {
            color: #d1d5db;
            font-size: 11pt;
        }
        .totals-total .totals-value {
            font-size: 14pt;
            font-weight: bold;
        }

        /* Payment Info */
        .payment-section {
            background: #f9fafb;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }
        .payment-title {
            font-size: 11pt;
            font-weight: bold;
            margin-bottom: 15px;
            color: #1f2937;
        }
        .payment-container {
            display: table;
            width: 100%;
        }
        .payment-grid {
            display: table-cell;
            width: 60%;
            vertical-align: top;
        }
        .payment-item {
            margin-bottom: 12px;
        }
        .payment-label {
            font-size: 8pt;
            text-transform: uppercase;
            color: #6b7280;
            margin-bottom: 3px;
        }
        .payment-value {
            font-family: 'DejaVu Sans Mono', monospace;
            font-size: 10pt;
            color: #1f2937;
        }
        .payment-qr {
            display: table-cell;
            width: 40%;
            text-align: right;
            vertical-align: top;
        }
        .qr-code-container {
            display: inline-block;
            padding: 10px;
            background: white;
            border-radius: 8px;
            border: 2px solid #e5e7eb;
        }
        .qr-code-label {
            font-size: 8pt;
            text-align: center;
            color: #6b7280;
            margin-top: 5px;
        }

        /* Notes */
        .notes-section {
            margin-bottom: 30px;
        }
        .notes-title {
            font-size: 9pt;
            text-transform: uppercase;
            color: #6b7280;
            margin-bottom: 8px;
        }
        .notes-content {
            font-size: 9pt;
            color: #4b5563;
            white-space: pre-line;
        }

        /* Footer */
        .footer {
            position: fixed;
            bottom: 30px;
            left: 40px;
            right: 40px;
            text-align: center;
            font-size: 8pt;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
            padding-top: 15px;
        }

        /* Peppol Badge */
        .peppol-badge {
            display: inline-block;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 9pt;
            font-weight: bold;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="page">
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                @if($company->logo_path)
                    <img src="{{ storage_path('app/public/' . $company->logo_path) }}" alt="{{ $company->name }}" style="max-width: 150px; max-height: 60px; margin-bottom: 10px;">
                @endif
                <div class="company-name">{{ $company->name }}</div>
                <div class="company-info">
                    @if($company->street){{ $company->street }} {{ $company->house_number }}<br>@endif
                    @if($company->postal_code || $company->city){{ $company->postal_code }} {{ $company->city }}<br>@endif
                    @if($company->vat_number)TVA: {{ $company->formatted_vat_number }}<br>@endif
                    @if($company->email){{ $company->email }}<br>@endif
                    @if($company->phone){{ $company->phone }}@endif
                </div>
            </div>
            <div class="header-right">
                <div class="invoice-title">FACTURE</div>
                <div class="invoice-number">{{ $invoice->invoice_number }}</div>
                <span class="status-badge status-{{ $invoice->status }}">{{ $invoice->status_label }}</span>
                @if($invoice->peppol_sent_at)
                    <br><span class="peppol-badge">Peppol</span>
                @endif
            </div>
        </div>

        <!-- Addresses -->
        <div class="addresses">
            <div class="address-block" style="margin-right: 20px;">
                <div class="address-title">Facturer à</div>
                <div class="address-name">{{ $invoice->partner->name }}</div>
                <div class="address-details">
                    @if($invoice->partner->street){{ $invoice->partner->street }} {{ $invoice->partner->house_number }}<br>@endif
                    @if($invoice->partner->postal_code || $invoice->partner->city){{ $invoice->partner->postal_code }} {{ $invoice->partner->city }}<br>@endif
                    @if($invoice->partner->country_code && $invoice->partner->country_code !== 'BE'){{ $invoice->partner->country_code }}<br>@endif
                    @if($invoice->partner->vat_number)TVA: {{ $invoice->partner->vat_number }}@endif
                </div>
            </div>
            <div style="display: table-cell; width: 4%;"></div>
            <div class="address-block">
                <div class="address-title">Livrer à</div>
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
                <div class="info-label">Date d'échéance</div>
                <div class="info-value">{{ $invoice->due_date ? $invoice->due_date->format('d/m/Y') : '-' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Référence</div>
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
                    <th class="text-center" style="width: 10%;">Quantité</th>
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
                        <td class="text-right">{{ number_format($line->unit_price, 2, ',', ' ') }} €</td>
                        <td class="text-center">{{ number_format($line->vat_rate, 0) }}%</td>
                        <td class="text-right">{{ number_format($line->total_excl_vat, 2, ',', ' ') }} €</td>
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
                    <div class="totals-value">{{ number_format($invoice->total_excl_vat, 2, ',', ' ') }} €</div>
                </div>
                @foreach($invoice->vatSummary() as $rate => $amount)
                    <div class="totals-row">
                        <div class="totals-label">TVA {{ $rate }}%</div>
                        <div class="totals-value">{{ number_format($amount, 2, ',', ' ') }} €</div>
                    </div>
                @endforeach
                <div class="totals-row totals-total">
                    <div class="totals-label">Total TTC</div>
                    <div class="totals-value">{{ number_format($invoice->total_incl_vat, 2, ',', ' ') }} €</div>
                </div>
            </div>
        </div>

        <!-- Payment Info -->
        <div class="payment-section">
            <div class="payment-title">Informations de paiement</div>
            <div class="payment-container">
                <div class="payment-grid">
                    <div class="payment-item">
                        <div class="payment-label">Bénéficiaire</div>
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
                        <div class="payment-label">Communication structurée</div>
                        <div class="payment-value">{{ $invoice->structured_communication }}</div>
                    </div>
                    <div class="payment-item">
                        <div class="payment-label">Montant à payer</div>
                        <div class="payment-value" style="font-weight: bold; font-size: 12pt;">{{ number_format($invoice->amount_due, $companyDecimalPlaces, ',', ' ') }} {{ $companyCurrency }}</div>
                    </div>
                </div>
                @if($company->default_iban && $invoice->amount_due > 0)
                <div class="payment-qr">
                    <div class="qr-code-container">
                        <img src="data:image/png;base64,{{ $qrCode }}" alt="QR Code Paiement" style="width: 150px; height: 150px; display: block;">
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
            @if($company->bic) | BIC: {{ $company->bic }}@endif
            <br>
            @if($company->website){{ $company->website }} | @endif
            @if($company->email){{ $company->email }} | @endif
            @if($company->phone){{ $company->phone }}@endif
        </div>
    </div>
</body>
</html>
