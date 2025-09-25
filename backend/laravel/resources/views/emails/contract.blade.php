<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rental Contract - {{ $contract->contract_number }}</title>
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
        .contract-details {
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
        .important-note {
            background-color: #fef3c7;
            border: 1px solid #f59e0b;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .important-note h4 {
            margin-top: 0;
            color: #d97706;
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
            <h2>Dear {{ $contract->serviceRequest->customer_name }},</h2>
            <p>Thank you for choosing AL IBDAL TRADING LLC for your car rental needs. Please find your rental contract attached to this email.</p>
        </div>

        <div class="contract-details">
            <h3 style="color: #2563eb; margin-top: 0;">Contract Summary</h3>
            
            <div class="detail-row">
                <span class="label">Contract Number:</span>
                <span>{{ $contract->contract_number }}</span>
            </div>
            
            <div class="detail-row">
                <span class="label">Contract Date:</span>
                <span>{{ $contract->created_at->format('d/m/Y') }}</span>
            </div>
            
            <div class="detail-row">
                <span class="label">Service Request:</span>
                <span>#{{ $contract->serviceRequest->id }}</span>
            </div>
            
            @if($contract->serviceRequest->car)
            <div class="detail-row">
                <span class="label">Vehicle:</span>
                <span>{{ $contract->serviceRequest->car->make }} {{ $contract->serviceRequest->car->model }} ({{ $contract->serviceRequest->car->year }})</span>
            </div>
            @endif
            
            <div class="detail-row">
                <span class="label">Rental Period:</span>
                <span>{{ \Carbon\Carbon::parse($contract->serviceRequest->start_date)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($contract->serviceRequest->end_date)->format('d/m/Y') }}</span>
            </div>
            
            <div class="detail-row">
                <span class="label">Rental Amount:</span>
                <span>OMR {{ number_format($contract->rental_amount, 2) }}</span>
            </div>
            
            <div class="detail-row">
                <span class="label">Security Deposit:</span>
                <span>OMR {{ number_format($contract->security_deposit, 2) }}</span>
            </div>
            
            <div class="detail-row" style="border-top: 2px solid #2563eb; padding-top: 10px; margin-top: 20px; font-size: 18px; font-weight: bold;">
                <span class="label">Total Amount Due:</span>
                <span>OMR {{ number_format($contract->rental_amount + $contract->security_deposit, 2) }}</span>
            </div>
        </div>

        <div class="important-note">
            <h4>⚠️ Important Contract Information</h4>
            <ul>
                <li><strong>Please review the contract carefully</strong> before signing</li>
                <li><strong>Bring a valid driving license</strong> when picking up the vehicle</li>
                <li><strong>Security deposit</strong> will be refunded upon vehicle return in good condition</li>
                <li><strong>Contact us immediately</strong> if you have any questions or concerns</li>
            </ul>
        </div>

        @if($contract->serviceRequest->branch)
        <div class="contract-details">
            <h3 style="color: #2563eb; margin-top: 0;">Pickup Location</h3>
            <p><strong>{{ $contract->serviceRequest->branch->name }}</strong><br>
            {{ $contract->serviceRequest->branch->address }}<br>
            Phone: {{ $contract->serviceRequest->branch->contact_phone }}<br>
            Email: {{ $contract->serviceRequest->branch->contact_email }}</p>
        </div>
        @endif

        @if($contract->additional_notes)
        <div class="contract-details">
            <h3 style="color: #2563eb; margin-top: 0;">Additional Notes</h3>
            <p>{{ $contract->additional_notes }}</p>
        </div>
        @endif

        <p><strong>Next Steps:</strong></p>
        <ol>
            <li>Review the attached contract thoroughly</li>
            <li>Print and sign the contract if you agree to the terms</li>
            <li>Bring the signed contract and your driving license to the pickup location</li>
            <li>Complete the payment and vehicle inspection process</li>
        </ol>

        <p>We look forward to serving you and ensuring you have a pleasant rental experience!</p>
        
        <div class="footer">
            <p><strong>AL IBDAL TRADING LLC</strong><br>
            Phone: 77307045 | Email: support@ALIBDALTRADING.COM<br>
            Sultanate of Oman</p>
            
            <p style="font-size: 12px; margin-top: 20px;">
                This email and any attachments are confidential and may be legally privileged.<br>
                If you are not the intended recipient, please delete this email and notify the sender.<br>
                This contract is governed by the laws of the Sultanate of Oman.
            </p>
        </div>
    </div>
</body>
</html>