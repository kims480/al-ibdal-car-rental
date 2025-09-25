<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice - {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
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
        .invoice-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .invoice-details, .customer-details {
            width: 48%;
        }
        .invoice-details h3, .customer-details h3 {
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
        .amount-breakdown {
            margin-top: 30px;
            float: right;
            width: 300px;
        }
        .breakdown-table {
            width: 100%;
            border-collapse: collapse;
        }
        .breakdown-table td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }
        .breakdown-table .label-col {
            font-weight: bold;
            text-align: right;
            width: 60%;
        }
        .breakdown-table .amount-col {
            text-align: right;
            width: 40%;
        }
        .total-row {
            background-color: #f8f9fa;
            font-weight: bold;
            font-size: 16px;
            border-top: 2px solid #2563eb;
        }
        .notes {
            clear: both;
            margin-top: 40px;
            padding-top: 20px;
        }
        .notes h4 {
            color: #2563eb;
            margin-bottom: 10px;
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
        .status-pending { background-color: #fef3c7; color: #d97706; }
        .status-sent { background-color: #dbeafe; color: #2563eb; }
        .status-paid { background-color: #dcfce7; color: #16a34a; }
        .status-cancelled { background-color: #fee2e2; color: #dc2626; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">AL IBDAL TRADING LLC</div>
        <div class="company-details">Car Rental Services</div>
        <div class="company-details">Phone: 77307045 | Email: support@ALIBDALTRADING.COM</div>
        <div class="company-details">Sultanate of Oman</div>
    </div>

    <div class="invoice-info">
        <div class="invoice-details">
            <h3>Invoice Details</h3>
            <div class="info-row">
                <span class="label">Invoice Number:</span>
                {{ $invoice->invoice_number }}
            </div>
            <div class="info-row">
                <span class="label">Issue Date:</span>
                {{ $invoice->created_at->format('d/m/Y') }}
            </div>
            <div class="info-row">
                <span class="label">Status:</span>
                <span class="status status-{{ $invoice->status }}">{{ ucfirst($invoice->status) }}</span>
            </div>
            <div class="info-row">
                <span class="label">Service Request:</span>
                #{{ $invoice->serviceRequest->id }}
            </div>
        </div>
        
        <div class="customer-details">
            <h3>Customer Details</h3>
            <div class="info-row">
                <span class="label">Name:</span>
                {{ $invoice->serviceRequest->customer_name }}
            </div>
            <div class="info-row">
                <span class="label">Phone:</span>
                {{ $invoice->serviceRequest->customer_phone }}
            </div>
            <div class="info-row">
                <span class="label">Email:</span>
                {{ $invoice->serviceRequest->email }}
            </div>
            <div class="info-row">
                <span class="label">City:</span>
                {{ $invoice->serviceRequest->customer_city }}
            </div>
        </div>
    </div>

    <div class="rental-details">
        <h3>Rental Details</h3>
        <table class="rental-table">
            <thead>
                <tr>
                    <th>Vehicle</th>
                    <th>Branch</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Duration</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        @if($invoice->serviceRequest->car)
                            {{ $invoice->serviceRequest->car->make }} {{ $invoice->serviceRequest->car->model }}<br>
                            <small>{{ $invoice->serviceRequest->car->year }} - {{ $invoice->serviceRequest->car->license_plate }}</small>
                        @else
                            Vehicle not assigned
                        @endif
                    </td>
                    <td>
                        @if($invoice->serviceRequest->branch)
                            {{ $invoice->serviceRequest->branch->name }}<br>
                            <small>{{ $invoice->serviceRequest->branch->contact_phone }}</small>
                        @else
                            Branch not assigned
                        @endif
                    </td>
                    <td>{{ \Carbon\Carbon::parse($invoice->serviceRequest->start_date)->format('d/m/Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($invoice->serviceRequest->end_date)->format('d/m/Y') }}</td>
                    <td>
                        {{ \Carbon\Carbon::parse($invoice->serviceRequest->start_date)->diffInDays(\Carbon\Carbon::parse($invoice->serviceRequest->end_date)) + 1 }} day(s)
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="amount-breakdown">
        <table class="breakdown-table">
            <tr>
                <td class="label-col">Rental Amount:</td>
                <td class="amount-col">OMR {{ number_format($invoice->amount, 2) }}</td>
            </tr>
            @if($invoice->tax_amount > 0)
            <tr>
                <td class="label-col">Tax:</td>
                <td class="amount-col">OMR {{ number_format($invoice->tax_amount, 2) }}</td>
            </tr>
            @endif
            @if($invoice->discount_amount > 0)
            <tr>
                <td class="label-col">Discount:</td>
                <td class="amount-col">-OMR {{ number_format($invoice->discount_amount, 2) }}</td>
            </tr>
            @endif
            <tr class="total-row">
                <td class="label-col">Total Amount:</td>
                <td class="amount-col">OMR {{ number_format($invoice->total_amount, 2) }}</td>
            </tr>
        </table>
    </div>

    @if($invoice->notes)
    <div class="notes">
        <h4>Additional Notes</h4>
        <p>{{ $invoice->notes }}</p>
    </div>
    @endif

    <div class="footer">
        <p><strong>AL IBDAL TRADING LLC</strong></p>
        <p>Thank you for choosing our car rental services!</p>
        <p>For any inquiries, please contact us at support@ALIBDALTRADING.COM or 77307045</p>
        <p><em>This is a computer-generated invoice.</em></p>
    </div>
</body>
</html>