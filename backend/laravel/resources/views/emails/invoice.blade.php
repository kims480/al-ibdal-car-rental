<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice - {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            background-color: #2563eb;
            color: white;
            padding: 20px;
            border-radius: 5px 5px 0 0;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .content {
            background-color: #f8f9fa;
            padding: 30px;
            border-radius: 0 0 5px 5px;
        }
        .greeting {
            margin-bottom: 20px;
        }
        .invoice-details {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .detail-row {
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
        }
        .label {
            font-weight: bold;
            color: #2563eb;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 14px;
            color: #666;
        }
        .cta-button {
            display: inline-block;
            background-color: #2563eb;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">AL IBDAL TRADING LLC</div>
        <div>Car Rental Services</div>
    </div>

    <div class="content">
        <div class="greeting">
            <h2>Dear {{ $invoice->getCustomerName() }},</h2>
            <p>Thank you for choosing AL IBDAL TRADING LLC for your car rental needs. Please find your invoice attached to this email.</p>
        </div>

        <div class="invoice-details">
            <h3 style="color: #2563eb; margin-top: 0;">Invoice Summary</h3>
            
            <div class="detail-row">
                <span class="label">Invoice Number:</span>
                <span>{{ $invoice->invoice_number }}</span>
            </div>
            
            <div class="detail-row">
                <span class="label">Issue Date:</span>
                <span>{{ $invoice->created_at->format('d/m/Y') }}</span>
            </div>
            
            @if($invoice->invoiceable_type === 'App\\Models\\ServiceRequest' || $invoice->serviceRequest)
                @php
                    $serviceRequest = $invoice->invoiceable_type === 'App\\Models\\ServiceRequest' ? $invoice->invoiceable : $invoice->serviceRequest;
                @endphp
                
                <div class="detail-row">
                    <span class="label">Service Request:</span>
                    <span>#{{ $serviceRequest->id }}</span>
                </div>
                
                @if($serviceRequest->car)
                <div class="detail-row">
                    <span class="label">Vehicle:</span>
                    <span>{{ $serviceRequest->car->make }} {{ $serviceRequest->car->model }} ({{ $serviceRequest->car->year }})</span>
                </div>
                @endif
                
                <div class="detail-row">
                    <span class="label">Rental Period:</span>
                    <span>{{ \Carbon\Carbon::parse($serviceRequest->start_date)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($serviceRequest->end_date)->format('d/m/Y') }}</span>
                </div>
            @endif

            @if($invoice->invoiceable_type === 'App\\Models\\Rental')
                @php
                    $rental = $invoice->invoiceable;
                @endphp
                
                <div class="detail-row">
                    <span class="label">Rental:</span>
                    <span>#{{ $rental->id }}</span>
                </div>
                
                @if($rental->car)
                <div class="detail-row">
                    <span class="label">Vehicle:</span>
                    <span>{{ $rental->car->make }} {{ $rental->car->model }} ({{ $rental->car->year }})</span>
                </div>
                @endif
                
                <div class="detail-row">
                    <span class="label">Rental Period:</span>
                    <span>{{ \Carbon\Carbon::parse($rental->start_date)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($rental->end_date)->format('d/m/Y') }}</span>
                </div>
            @endif
            
            <div class="detail-row" style="border-top: 2px solid #2563eb; padding-top: 10px; margin-top: 20px; font-size: 18px; font-weight: bold;">
                <span class="label">Total Amount:</span>
                <span>OMR {{ number_format($invoice->total_amount, 2) }}</span>
            </div>
        </div>

        <p><strong>Payment Instructions:</strong></p>
        <p>Please settle this invoice within 7 days of receipt. You can make payments through bank transfer or by visiting our branch office.</p>

        @php
            $branch = null;
            if($invoice->invoiceable_type === 'App\\Models\\ServiceRequest' || $invoice->serviceRequest) {
                $serviceRequest = $invoice->invoiceable_type === 'App\\Models\\ServiceRequest' ? $invoice->invoiceable : $invoice->serviceRequest;
                $branch = $serviceRequest->branch;
            } elseif($invoice->invoiceable_type === 'App\\Models\\Rental') {
                $rental = $invoice->invoiceable;
                $branch = $rental->car ? $rental->car->branch : null;
            }
        @endphp

        @if($branch)
        <div class="invoice-details">
            <h3 style="color: #2563eb; margin-top: 0;">Branch Information</h3>
            <p><strong>{{ $branch->name }}</strong><br>
            {{ $branch->address }}<br>
            Phone: {{ $branch->contact_phone }}<br>
            Email: {{ $branch->contact_email }}</p>
        </div>
        @endif

        @if($invoice->notes)
        <div class="invoice-details">
            <h3 style="color: #2563eb; margin-top: 0;">Additional Notes</h3>
            <p>{{ $invoice->notes }}</p>
        </div>
        @endif

        <p>If you have any questions regarding this invoice, please don't hesitate to contact us.</p>
        
        <p>Thank you for your business!</p>
        
        <div class="footer">
            <p><strong>AL IBDAL TRADING LLC</strong><br>
            Phone: 77307045 | Email: support@ALIBDALTRADING.COM<br>
            Sultanate of Oman</p>
            
            <p style="font-size: 12px; margin-top: 20px;">
                This email and any attachments are confidential and may be legally privileged.<br>
                If you are not the intended recipient, please delete this email and notify the sender.
            </p>
        </div>
    </div>
</body>
</html>