<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $type === 'created' ? 'New Service Request' : 'Service Request Update' }} - AL IBDAL TRADING LLC</title>
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
            background-color: #2c3e50;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f8f9fa;
            padding: 20px;
            border: 1px solid #dee2e6;
        }
        .footer {
            background-color: #6c757d;
            color: white;
            padding: 15px;
            text-align: center;
            border-radius: 0 0 5px 5px;
        }
        .info-row {
            margin-bottom: 10px;
        }
        .label {
            font-weight: bold;
            display: inline-block;
            width: 120px;
        }
        .status {
            padding: 5px 10px;
            border-radius: 3px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-pending { background-color: #ffc107; color: #000; }
        .status-assigned { background-color: #17a2b8; color: #fff; }
        .status-confirmed { background-color: #28a745; color: #fff; }
        .status-picked_up { background-color: #007bff; color: #fff; }
        .status-returned { background-color: #6f42c1; color: #fff; }
        .status-completed { background-color: #28a745; color: #fff; }
        .status-cancelled { background-color: #dc3545; color: #fff; }
    </style>
</head>
<body>
    <div class="header">
        <h1>AL IBDAL TRADING LLC</h1>
        <h2>
            @if($type === 'created')
                New Service Request
            @elseif($type === 'assigned')
                Service Request Assigned
            @elseif($type === 'confirmed')
                Rental Confirmed
            @elseif($type === 'picked_up')
                Vehicle Picked Up
            @elseif($type === 'returned')
                Vehicle Returned
            @elseif($type === 'completed')
                Rental Completed
            @else
                Service Request Update
            @endif
        </h2>
    </div>

    <div class="content">
        <p>Dear {{ $recipient->name }},</p>

        @if($type === 'created')
            <p>A new service request has been submitted and requires attention.</p>
        @elseif($type === 'assigned')
            <p>A service request has been assigned to you. Please review the details below and take appropriate action.</p>
        @elseif($type === 'confirmed')
            <p>Your rental request has been confirmed! Here are your rental details:</p>
        @else
            <p>There has been an update to your service request. Please see the details below:</p>
        @endif

        <h3>Service Request Details:</h3>
        <div class="info-row">
            <span class="label">Request ID:</span> #{{ $serviceRequest->id }}
        </div>
        <div class="info-row">
            <span class="label">Customer:</span> {{ $serviceRequest->customer_name }}
        </div>
        <div class="info-row">
            <span class="label">Phone:</span> {{ $serviceRequest->customer_phone }}
        </div>
        <div class="info-row">
            <span class="label">Location:</span> {{ $serviceRequest->customer_location }}
        </div>
        <div class="info-row">
            <span class="label">Rental Period:</span> {{ $serviceRequest->rental_start->format('M d, Y') }} to {{ $serviceRequest->rental_end->format('M d, Y') }}
        </div>
        <div class="info-row">
            <span class="label">Status:</span> 
            <span class="status status-{{ $serviceRequest->status }}">{{ ucfirst(str_replace('_', ' ', $serviceRequest->status)) }}</span>
        </div>

        @if($serviceRequest->branch)
        <div class="info-row">
            <span class="label">Branch:</span> {{ $serviceRequest->branch->name }}
        </div>
        <div class="info-row">
            <span class="label">Branch Contact:</span> {{ $serviceRequest->branch->contact_phone }}
        </div>
        @endif

        @if($serviceRequest->car)
        <div class="info-row">
            <span class="label">Assigned Car:</span> {{ $serviceRequest->car->make }} {{ $serviceRequest->car->model }} ({{ $serviceRequest->car->registration }})
        </div>
        @endif

        @if($serviceRequest->partner)
        <div class="info-row">
            <span class="label">Assigned To:</span> {{ $serviceRequest->partner->name }}
        </div>
        @endif

        @if($serviceRequest->notes)
        <div class="info-row">
            <span class="label">Notes:</span> {{ $serviceRequest->notes }}
        </div>
        @endif

        @if($type === 'confirmed' && $serviceRequest->branch)
        <h3>Pickup Instructions:</h3>
        <p><strong>Branch Address:</strong> {{ $serviceRequest->branch->address }}</p>
        <p><strong>Contact Number:</strong> {{ $serviceRequest->branch->contact_phone }}</p>
        <p><strong>Email:</strong> {{ $serviceRequest->branch->contact_email }}</p>
        <p>Please bring a valid driver's license and the required documents for vehicle pickup.</p>
        @endif

        @if($type === 'assigned')
        <p><strong>Next Steps:</strong></p>
        <ul>
            <li>Review the customer requirements</li>
            <li>Confirm car availability</li>
            <li>Contact the customer to arrange pickup</li>
            <li>Update the request status once confirmed</li>
        </ul>
        @endif
    </div>

    <div class="footer">
        <p>&copy; {{ date('Y') }} AL IBDAL TRADING LLC - Sultanate of Oman</p>
        <p>For support, please contact us at support@ALIBDALTRADING.COM or call 77307045</p>
    </div>
</body>
</html>