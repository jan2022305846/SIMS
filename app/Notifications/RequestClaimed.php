<?php

namespace App\Notifications;

use App\Models\Request as SupplyRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RequestClaimed extends Notification
{
    use Queueable;

    protected SupplyRequest $request;

    /**
     * Create a new notification instance.
     */
    public function __construct(SupplyRequest $request)
    {
        $this->request = $request;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $request = $this->request;

        return (new MailMessage)
            ->subject('Request Claimed - Supply Office System')
            ->greeting("Hello {$notifiable->name},")
            ->line("Your request has been successfully claimed!")
            ->line("**Request Details:**")
            ->line("- Item: {$request->item->name}")
            ->line("- Quantity: {$request->quantity}")
            ->line("- Purpose: {$request->purpose}")
            ->line("- Claim Slip Number: {$request->claim_slip_number}")
            ->line('Please keep this claim slip number for your records.')
            ->action('Download Claim Slip', route('faculty.requests.download-claim-slip', $request))
            ->line('Thank you for using the Supply Office System!')
            ->salutation('Best regards, Supply Office Team');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'request_id' => $this->request->id,
            'item_name' => $this->request->item->name,
            'quantity' => $this->request->quantity,
            'claim_slip_number' => $this->request->claim_slip_number,
        ];
    }
}
