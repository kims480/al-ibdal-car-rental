<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rental Contract - {{ $contract->contract_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
            line-height: 1.6;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #2563eb;
            padding-bottom: 20px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 10px;
        }
        .company-details {
            font-size: 14px;
            color: #666;
            margin-bottom: 5px;
        }
        .contract-title {
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 30px;
            text-transform: uppercase;
        }
        .contract-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .contract-details, .customer-details {
            width: 48%;
        }
        .contract-details h3, .customer-details h3 {
            color: #2563eb;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }
        .info-row {
            margin-bottom: 8px;
        }
        .label {
            font-weight: bold;
            display: inline-block;
            width: 120px;
        }
        .rental-details {
            margin-bottom: 30px;
        }
        .rental-details h3 {
            color: #2563eb;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }
        .rental-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .rental-table th, .rental-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        .rental-table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .financial-summary {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
        }
        .financial-summary h3 {
            color: #2563eb;
            margin-top: 0;
        }
        .amount-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 5px 0;
            border-bottom: 1px solid #ddd;
        }
        .amount-row.total {
            font-weight: bold;
            font-size: 16px;
            border-top: 2px solid #2563eb;
            border-bottom: 2px solid #2563eb;
        }
        .terms-conditions {
            margin-bottom: 30px;
        }
        .terms-conditions h3 {
            color: #2563eb;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }
        .terms-text {
            white-space: pre-line;
            font-size: 12px;
            line-height: 1.5;
        }
        .signature-section {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
        }
        .signature-box {
            width: 45%;
            text-align: center;
        }
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 50px;
            padding-top: 10px;
            font-weight: bold;
        }
        .signature-date {
            margin-top: 10px;
            font-size: 12px;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
        .status {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-draft { background-color: #f3f4f6; color: #6b7280; }
        .status-sent { background-color: #dbeafe; color: #2563eb; }
        .status-signed { background-color: #dcfce7; color: #16a34a; }
        .status-cancelled { background-color: #fee2e2; color: #dc2626; }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">AL IBDAL TRADING LLC</div>
        <div class="company-details">Car Rental Services</div>
        <div class="company-details">Phone: 77307045 | Email: support@ALIBDALTRADING.COM</div>
        <div class="company-details">Sultanate of Oman</div>
    </div>

    <div class="contract-title">Vehicle Rental Agreement</div>

    <div class="contract-info">
        <div class="contract-details">
            <h3>Contract Details</h3>
            <div class="info-row">
                <span class="label">Contract Number:</span>
                {{ $contract->contract_number }}
            </div>
            <div class="info-row">
                <span class="label">Contract Date:</span>
                {{ $contract->created_at->format('d/m/Y') }}
            </div>
            <div class="info-row">
                <span class="label">Status:</span>
                <span class="status status-{{ $contract->status }}">{{ ucfirst($contract->status) }}</span>
            </div>
            <div class="info-row">
                <span class="label">Service Request:</span>
                #{{ $contract->serviceRequest->id }}
            </div>
        </div>
        
        <div class="customer-details">
            <h3>Customer Details (Renter)</h3>
            <div class="info-row">
                <span class="label">Name:</span>
                {{ $contract->serviceRequest->customer_name }}
            </div>
            <div class="info-row">
                <span class="label">Phone:</span>
                {{ $contract->serviceRequest->customer_phone }}
            </div>
            <div class="info-row">
                <span class="label">Email:</span>
                {{ $contract->serviceRequest->email }}
            </div>
            <div class="info-row">
                <span class="label">City:</span>
                {{ $contract->serviceRequest->customer_city }}
            </div>
        </div>
    </div>

    <div class="rental-details">
        <h3>Vehicle & Rental Details</h3>
        <table class="rental-table">
            <thead>
                <tr>
                    <th>Vehicle Information</th>
                    <th>Rental Period</th>
                    <th>Branch Information</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        @if($contract->serviceRequest->car)
                            <strong>{{ $contract->serviceRequest->car->make }} {{ $contract->serviceRequest->car->model }}</strong><br>
                            Year: {{ $contract->serviceRequest->car->year }}<br>
                            License Plate: {{ $contract->serviceRequest->car->license_plate }}<br>
                            Color: {{ $contract->serviceRequest->car->color ?? 'N/A' }}<br>
                            Status: {{ ucfirst($contract->serviceRequest->car->status) }}
                        @else
                            Vehicle not assigned
                        @endif
                    </td>
                    <td>
                        <strong>Start:</strong> {{ \Carbon\Carbon::parse($contract->serviceRequest->start_date)->format('d/m/Y') }}<br>
                        <strong>End:</strong> {{ \Carbon\Carbon::parse($contract->serviceRequest->end_date)->format('d/m/Y') }}<br>
                        <strong>Duration:</strong> {{ \Carbon\Carbon::parse($contract->serviceRequest->start_date)->diffInDays(\Carbon\Carbon::parse($contract->serviceRequest->end_date)) + 1 }} day(s)
                    </td>
                    <td>
                        @if($contract->serviceRequest->branch)
                            <strong>{{ $contract->serviceRequest->branch->name }}</strong><br>
                            {{ $contract->serviceRequest->branch->address }}<br>
                            Phone: {{ $contract->serviceRequest->branch->contact_phone }}<br>
                            Email: {{ $contract->serviceRequest->branch->contact_email }}
                        @else
                            Branch not assigned
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="financial-summary">
        <h3>Financial Summary</h3>
        <div class="amount-row">
            <span>Rental Amount:</span>
            <span>OMR {{ number_format($contract->rental_amount, 2) }}</span>
        </div>
        <div class="amount-row">
            <span>Security Deposit:</span>
            <span>OMR {{ number_format($contract->security_deposit, 2) }}</span>
        </div>
        <div class="amount-row total">
            <span>Total Amount Due:</span>
            <span>OMR {{ number_format($contract->rental_amount + $contract->security_deposit, 2) }}</span>
        </div>
    </div>

    @if($contract->additional_notes)
    <div class="rental-details">
        <h3>Additional Notes</h3>
        <p>{{ $contract->additional_notes }}</p>
    </div>
    @endif

    <div class="page-break"></div>

    <div class="terms-conditions">
        <h3>Terms and Conditions</h3>
        <div class="terms-text">{{ $contract->terms_conditions }}</div>
    </div>

    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-line">Customer Signature</div>
            <div class="signature-date">Date: _______________</div>
            <div style="margin-top: 10px;">
                <strong>{{ $contract->serviceRequest->customer_name }}</strong>
            </div>
        </div>
        <div class="signature-box">
            <div class="signature-line">Company Representative</div>
            <div class="signature-date">Date: _______________</div>
            <div style="margin-top: 10px;">
                <strong>AL IBDAL TRADING LLC</strong>
            </div>
        </div>
    </div>

    <div class="footer">
        <p><strong>AL IBDAL TRADING LLC</strong></p>
        <p>This contract is governed by the laws of the Sultanate of Oman</p>
        <p>For any inquiries, please contact us at support@ALIBDALTRADING.COM or 77307045</p>
        <p><em>This is a legally binding document. Please read carefully before signing.</em></p>
    </div>
</body>
</html>