<?php

namespace App\Mail;

use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ServiceRequestNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $serviceRequest;
    public $recipient;
    public $type;

    /**
     * Create a new message instance.
     */
    public function __construct(ServiceRequest $serviceRequest, User $recipient, string $type)
    {
        $this->serviceRequest = $serviceRequest;
        $this->recipient = $recipient;
        $this->type = $type; // 'created', 'assigned', 'confirmed', etc.
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subjects = [
            'created' => 'New Service Request - AL IBDAL TRADING LLC',
            'assigned' => 'Service Request Assigned - AL IBDAL TRADING LLC',
            'confirmed' => 'Rental Confirmation - AL IBDAL TRADING LLC',
            'picked_up' => 'Vehicle Picked Up - AL IBDAL TRADING LLC',
            'returned' => 'Vehicle Returned - AL IBDAL TRADING LLC',
            'completed' => 'Rental Completed - AL IBDAL TRADING LLC',
        ];

        return new Envelope(
            subject: $subjects[$this->type] ?? 'Service Request Update - AL IBDAL TRADING LLC',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.service-request-notification',
            with: [
                'serviceRequest' => $this->serviceRequest,
                'recipient' => $this->recipient,
                'type' => $this->type,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
