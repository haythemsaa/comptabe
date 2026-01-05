<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Devis {{ $quote->quote_number }}</title>
    @php
        $primaryColor = $templateColors['primary'] ?? '#0891b2';
        $secondaryColor = $templateColors['secondary'] ?? '#1e293b';
    @endphp
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
            color: {{ $secondaryColor }};
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
            color: {{ $primaryColor }};
            margin-bottom: 5px;
        }
        .company-info {
            font-size: 9pt;
            color: #6b7280;
        }
        .quote-title {
            font-size: 24pt;
            font-weight: bold;
            color: {{ $secondaryColor }};
            margin-bottom: 10px;
        }
        .quote-number {
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
        .status-sent { background: #dbeafe; color: #1e40af; }
        .status-accepted { background: #d1fae5; color: #065f46; }
        .status-rejected { background: #fee2e2; color: #991b1b; }
        .status-converted { background: #e0e7ff; color: #3730a3; }

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
            background: #f8fafc;
            border-radius: 8px;
            padding: 15px;
            border: 1px solid #e2e8f0;
        }
        .info-item {
            display: table-cell;
            text-align: center;
            padding: 0 10px;
            border-right: 1px solid #e2e8f0;
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
            color: {{ $secondaryColor }};
        }

        /* Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .items-table thead th {
            background: {{ $primaryColor }};
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
            background: {{ $primaryColor }};
            color: white;
            border-radius: 8px;
            margin-top: 10px;
        }
        .totals-total .totals-label {
            color: rgba(255,255,255,0.8);
            font-size: 11pt;
        }
        .totals-total .totals-value {
            font-size: 14pt;
            font-weight: bold;
        }

        /* Validity Info */
        .validity-section {
            background: #fef3c7;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 30px;
            text-align: center;
        }
        .validity-text {
            font-size: 10pt;
            color: #92400e;
        }
        .validity-date {
            font-weight: bold;
        }

        /* Notes */
        .notes-section {
            margin-bottom: 20px;
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

        /* Terms */
        .terms-section {
            background: #f9fafb;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 30px;
        }
        .terms-title {
            font-size: 9pt;
            text-transform: uppercase;
            color: #6b7280;
            margin-bottom: 8px;
        }
        .terms-content {
            font-size: 8pt;
            color: #6b7280;
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

        /* Signature Block */
        .signature-section {
            display: table;
            width: 100%;
            margin-top: 40px;
            margin-bottom: 30px;
        }
        .signature-block {
            display: table-cell;
            width: 45%;
            padding: 20px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
        }
        .signature-title {
            font-size: 9pt;
            text-transform: uppercase;
            color: #6b7280;
            margin-bottom: 40px;
        }
        .signature-line {
            border-top: 1px solid #9ca3af;
            padding-top: 5px;
            font-size: 9pt;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="page">
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                @if($quote->company && $quote->company->logo_path)
                    <img src="{{ storage_path('app/public/' . $quote->company->logo_path) }}" alt="{{ $quote->company->name }}" style="max-width: 150px; max-height: 60px; margin-bottom: 10px;">
                @endif
                <div class="company-name">{{ $quote->company->name ?? config('app.name') }}</div>
                <div class="company-info">
                    @if($quote->company)
                        @if($quote->company->street){{ $quote->company->street }} {{ $quote->company->house_number }}<br>@endif
                        @if($quote->company->postal_code || $quote->company->city){{ $quote->company->postal_code }} {{ $quote->company->city }}<br>@endif
                        @if($quote->company->vat_number)TVA: {{ $quote->company->formatted_vat_number }}<br>@endif
                        @if($quote->company->email){{ $quote->company->email }}<br>@endif
                        @if($quote->company->phone){{ $quote->company->phone }}@endif
                    @endif
                </div>
            </div>
            <div class="header-right">
                <div class="quote-title">DEVIS</div>
                <div class="quote-number">{{ $quote->quote_number }}</div>
                <span class="status-badge status-{{ $quote->status }}">{{ $quote->status_label }}</span>
            </div>
        </div>

        <!-- Addresses -->
        <div class="addresses">
            <div class="address-block" style="margin-right: 20px;">
                <div class="address-title">Client</div>
                <div class="address-name">{{ $quote->partner->name }}</div>
                <div class="address-details">
                    @if($quote->partner->street){{ $quote->partner->street }} {{ $quote->partner->house_number }}<br>@endif
                    @if($quote->partner->postal_code || $quote->partner->city){{ $quote->partner->postal_code }} {{ $quote->partner->city }}<br>@endif
                    @if($quote->partner->country_code && $quote->partner->country_code !== 'BE'){{ $quote->partner->country_code }}<br>@endif
                    @if($quote->partner->vat_number)TVA: {{ $quote->partner->vat_number }}@endif
                </div>
            </div>
            <div style="display: table-cell; width: 4%;"></div>
            <div class="address-block">
                <div class="address-title">Contact</div>
                <div class="address-details">
                    @if($quote->partner->email)Email: {{ $quote->partner->email }}<br>@endif
                    @if($quote->partner->phone)Tel: {{ $quote->partner->phone }}@endif
                </div>
            </div>
        </div>

        <!-- Info Grid -->
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">Date du devis</div>
                <div class="info-value">{{ $quote->quote_date->format('d/m/Y') }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Valide jusqu'au</div>
                <div class="info-value">{{ $quote->valid_until ? $quote->valid_until->format('d/m/Y') : '-' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Reference</div>
                <div class="info-value">{{ $quote->reference ?? '-' }}</div>
            </div>
        </div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 45%;">Description</th>
                    <th class="text-center" style="width: 10%;">Quantite</th>
                    <th class="text-right" style="width: 15%;">Prix unit.</th>
                    <th class="text-center" style="width: 10%;">TVA</th>
                    <th class="text-right" style="width: 20%;">Total HT</th>
                </tr>
            </thead>
            <tbody>
                @foreach($quote->lines as $line)
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
                        <td class="text-right">{{ number_format($line->line_total, 2, ',', ' ') }} EUR</td>
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
                    <div class="totals-value">{{ number_format($quote->total_excl_vat, 2, ',', ' ') }} EUR</div>
                </div>
                <div class="totals-row">
                    <div class="totals-label">Total TVA</div>
                    <div class="totals-value">{{ number_format($quote->total_vat, 2, ',', ' ') }} EUR</div>
                </div>
                <div class="totals-row totals-total">
                    <div class="totals-label">Total TTC</div>
                    <div class="totals-value">{{ number_format($quote->total_incl_vat, 2, ',', ' ') }} EUR</div>
                </div>
            </div>
        </div>

        <!-- Validity Notice -->
        @if($quote->valid_until)
            <div class="validity-section">
                <div class="validity-text">
                    Ce devis est valable jusqu'au <span class="validity-date">{{ $quote->valid_until->format('d/m/Y') }}</span>.
                    Passe cette date, les prix et conditions peuvent etre revises.
                </div>
            </div>
        @endif

        <!-- Notes -->
        @if($quote->notes)
            <div class="notes-section">
                <div class="notes-title">Notes</div>
                <div class="notes-content">{{ $quote->notes }}</div>
            </div>
        @endif

        <!-- Terms -->
        @if($quote->terms)
            <div class="terms-section">
                <div class="terms-title">Conditions generales</div>
                <div class="terms-content">{{ $quote->terms }}</div>
            </div>
        @endif

        <!-- Signature Section -->
        <div class="signature-section">
            <div class="signature-block" style="margin-right: 20px;">
                <div class="signature-title">Pour le fournisseur</div>
                <div class="signature-line">Date et signature</div>
            </div>
            <div style="display: table-cell; width: 10%;"></div>
            <div class="signature-block">
                <div class="signature-title">Bon pour accord - Le client</div>
                <div class="signature-line">Date et signature</div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            @if($quote->company)
                {{ $quote->company->name }} | TVA: {{ $quote->company->formatted_vat_number ?? '' }}
                @if($quote->company->iban) | IBAN: {{ $quote->company->formatted_iban }}@endif
                @if($quote->company->bic) | BIC: {{ $quote->company->bic }}@endif
                <br>
                @if($quote->company->website){{ $quote->company->website }} | @endif
                @if($quote->company->email){{ $quote->company->email }} | @endif
                @if($quote->company->phone){{ $quote->company->phone }}@endif
            @endif
        </div>
    </div>
</body>
</html>
