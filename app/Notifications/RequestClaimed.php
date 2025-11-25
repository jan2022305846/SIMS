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

        // Ensure requestItems are loaded
        if (!$request->relationLoaded('requestItems')) {
            $request->load('requestItems');
        }

        // Manually load itemable relationships for each request item
        foreach ($request->requestItems as $requestItem) {
            if (!$requestItem->relationLoaded('itemable')) {
                if ($requestItem->item_type === 'consumable') {
                    $itemable = \App\Models\Consumable::find($requestItem->item_id);
                } elseif ($requestItem->item_type === 'non_consumable') {
                    $itemable = \App\Models\NonConsumable::find($requestItem->item_id);
                } else {
                    $itemable = null;
                }
                $requestItem->setRelation('itemable', $itemable);
            }
        }

        $mail = (new MailMessage)
            ->subject('Request Claimed - Supply Office System')
            ->greeting("Hello {$notifiable->name},")
            ->line("Your request has been successfully claimed!")
            ->line("**Request Details:**");

        // Add each item to the email
        foreach ($request->requestItems as $requestItem) {
            $itemName = $requestItem->itemable ? $requestItem->itemable->name : 'Unknown Item';
            $mail->line("- Item: {$itemName} (Quantity: {$requestItem->quantity})");
        }

        $mail->line("- Purpose: {$request->purpose}")
            ->line("- Claim Slip Number: {$request->claim_slip_number}")
            ->line('Please keep this claim slip number for your records.')
            ->action('Download Claim Slip', route('faculty.requests.download-claim-slip', $request))
            ->line('Thank you for using the Supply Office System!')
            ->salutation('Best regards, Supply Office Team');

        return $mail;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $request = $this->request;

        // Ensure requestItems are loaded
        if (!$request->relationLoaded('requestItems')) {
            $request->load('requestItems');
        }

        // Manually load itemable relationships for each request item
        foreach ($request->requestItems as $requestItem) {
            if (!$requestItem->relationLoaded('itemable')) {
                if ($requestItem->item_type === 'consumable') {
                    $itemable = \App\Models\Consumable::find($requestItem->item_id);
                } elseif ($requestItem->item_type === 'non_consumable') {
                    $itemable = \App\Models\NonConsumable::find($requestItem->item_id);
                } else {
                    $itemable = null;
                }
                $requestItem->setRelation('itemable', $itemable);
            }
        }

        $items = [];
        foreach ($request->requestItems as $requestItem) {
            $items[] = [
                'name' => $requestItem->itemable ? $requestItem->itemable->name : 'Unknown Item',
                'quantity' => $requestItem->quantity,
            ];
        }

        return [
            'request_id' => $this->request->id,
            'items' => $items,
            'total_quantity' => $request->getTotalItems(),
            'claim_slip_number' => $this->request->claim_slip_number,
        ];
    }
}
