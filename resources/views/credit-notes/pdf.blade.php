<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Note de crédit {{ $creditNote->credit_note_number }}</title>
    @php
        $primaryColor = $templateColors['primary'] ?? '#6366f1';
        $secondaryColor = $templateColors['secondary'] ?? '#1e293b';
        $creditColor = '#dc2626'; // Red remains for credit notes
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
        .document-title {
            font-size: 24pt;
            font-weight: bold;
            color: {{ $creditColor }};
            margin-bottom: 10px;
        }
        .document-number {
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
        .status-applied { background: #d1fae5; color: #065f46; }

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
            background: #fef2f2;
            border-radius: 8px;
            padding: 15px;
        }
        .info-item {
            display: table-cell;
            text-align: center;
            padding: 0 10px;
            border-right: 1px solid #fecaca;
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

        /* Reason Box */
        .reason-box {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 30px;
        }
        .reason-title {
            font-size: 8pt;
            text-transform: uppercase;
            color: #6b7280;
            margin-bottom: 5px;
        }
        .reason-content {
            font-size: 10pt;
            color: #1f2937;
        }

        /* Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .items-table thead th {
            background: {{ $creditColor }};
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
        .amount-negative {
            color: {{ $creditColor }};
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
            color: {{ $creditColor }};
        }
        .totals-total {
            background: {{ $creditColor }};
            color: white;
            border-radius: 8px;
            margin-top: 10px;
        }
        .totals-total .totals-label {
            color: #fecaca;
            font-size: 11pt;
        }
        .totals-total .totals-value {
            font-size: 14pt;
            font-weight: bold;
            color: white;
        }

        /* Linked Invoice */
        .linked-invoice {
            background: #f3f4f6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 30px;
        }
        .linked-invoice-title {
            font-size: 9pt;
            text-transform: uppercase;
            color: #6b7280;
            margin-bottom: 8px;
        }
        .linked-invoice-content {
            font-size: 10pt;
            font-weight: bold;
            color: #1f2937;
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

        /* Credit Note Badge */
        .credit-badge {
            display: inline-block;
            background: {{ $creditColor }};
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 9pt;
            font-weight: bold;
            margin-top: 10px;
        }

        /* Footer with company branding */
        .footer-brand {
            color: {{ $primaryColor }};
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="page">
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                @if($creditNote->company && $creditNote->company->logo_path)
                    <img src="{{ storage_path('app/public/' . $creditNote->company->logo_path) }}" alt="{{ $creditNote->company->name }}" style="max-width: 150px; max-height: 60px; margin-bottom: 10px;">
                @endif
                <div class="company-name">{{ $creditNote->company->name ?? '' }}</div>
                <div class="company-info">
                    @if($creditNote->company?->street){{ $creditNote->company->street }} {{ $creditNote->company->house_number }}<br>@endif
                    @if($creditNote->company?->postal_code || $creditNote->company?->city){{ $creditNote->company->postal_code }} {{ $creditNote->company->city }}<br>@endif
                    @if($creditNote->company?->vat_number)TVA: {{ $creditNote->company->formatted_vat_number }}<br>@endif
                    @if($creditNote->company?->email){{ $creditNote->company->email }}<br>@endif
                    @if($creditNote->company?->phone){{ $creditNote->company->phone }}@endif
                </div>
            </div>
            <div class="header-right">
                <div class="document-title">NOTE DE CRÉDIT</div>
                <div class="document-number">{{ $creditNote->credit_note_number }}</div>
                <span class="status-badge status-{{ $creditNote->status }}">{{ $creditNote->status_label }}</span>
                <br><span class="credit-badge">AVOIR</span>
            </div>
        </div>

        <!-- Addresses -->
        <div class="addresses">
            <div class="address-block" style="margin-right: 20px;">
                <div class="address-title">De</div>
                <div class="address-name">{{ $creditNote->company->name ?? '' }}</div>
                <div class="address-details">
                    @if($creditNote->company?->street){{ $creditNote->company->street }} {{ $creditNote->company->house_number }}<br>@endif
                    @if($creditNote->company?->postal_code || $creditNote->company?->city){{ $creditNote->company->postal_code }} {{ $creditNote->company->city }}<br>@endif
                    @if($creditNote->company?->vat_number)TVA: {{ $creditNote->company->vat_number }}@endif
                </div>
            </div>
            <div style="display: table-cell; width: 4%;"></div>
            <div class="address-block">
                <div class="address-title">Créditer à</div>
                <div class="address-name">{{ $creditNote->partner->name }}</div>
                <div class="address-details">
                    @if($creditNote->partner->street){{ $creditNote->partner->street }} {{ $creditNote->partner->house_number }}<br>@endif
                    @if($creditNote->partner->postal_code || $creditNote->partner->city){{ $creditNote->partner->postal_code }} {{ $creditNote->partner->city }}<br>@endif
                    @if($creditNote->partner->country_code && $creditNote->partner->country_code !== 'BE'){{ $creditNote->partner->country_code }}<br>@endif
                    @if($creditNote->partner->vat_number)TVA: {{ $creditNote->partner->vat_number }}@endif
                </div>
            </div>
        </div>

        <!-- Info Grid -->
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">Date</div>
                <div class="info-value">{{ $creditNote->credit_note_date->format('d/m/Y') }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Référence</div>
                <div class="info-value">{{ $creditNote->reference ?? '-' }}</div>
            </div>
            @if($creditNote->invoice)
                <div class="info-item">
                    <div class="info-label">Facture liée</div>
                    <div class="info-value">{{ $creditNote->invoice->invoice_number }}</div>
                </div>
            @endif
            @if($creditNote->structured_communication)
                <div class="info-item">
                    <div class="info-label">Communication</div>
                    <div class="info-value" style="font-family: 'DejaVu Sans Mono', monospace;">{{ $creditNote->structured_communication }}</div>
                </div>
            @endif
        </div>

        <!-- Reason -->
        @if($creditNote->reason)
            <div class="reason-box">
                <div class="reason-title">Motif de la note de crédit</div>
                <div class="reason-content">{{ $creditNote->reason }}</div>
            </div>
        @endif

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
                @foreach($creditNote->lines as $line)
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
                        <td class="text-right amount-negative">-{{ number_format($line->line_total, 2, ',', ' ') }} €</td>
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
                    <div class="totals-value">-{{ number_format($creditNote->total_excl_vat, 2, ',', ' ') }} €</div>
                </div>
                @foreach($creditNote->vatSummary() as $rate => $amount)
                    <div class="totals-row">
                        <div class="totals-label">TVA {{ $rate }}%</div>
                        <div class="totals-value">-{{ number_format($amount, 2, ',', ' ') }} €</div>
                    </div>
                @endforeach
                <div class="totals-row totals-total">
                    <div class="totals-label">Total TTC</div>
                    <div class="totals-value">-{{ number_format($creditNote->total_incl_vat, 2, ',', ' ') }} €</div>
                </div>
            </div>
        </div>

        <!-- Notes -->
        @if($creditNote->notes)
            <div class="notes-section">
                <div class="notes-title">Notes</div>
                <div class="notes-content">{{ $creditNote->notes }}</div>
            </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            {{ $creditNote->company->name ?? '' }} | TVA: {{ $creditNote->company->formatted_vat_number ?? '' }} | IBAN: {{ $creditNote->company->formatted_iban ?? '' }}
            @if($creditNote->company?->bic) | BIC: {{ $creditNote->company->bic }}@endif
            <br>
            @if($creditNote->company?->website){{ $creditNote->company->website }} | @endif
            @if($creditNote->company?->email){{ $creditNote->company->email }} | @endif
            @if($creditNote->company?->phone){{ $creditNote->company->phone }}@endif
        </div>
    </div>
</body>
</html>
