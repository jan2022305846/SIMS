<?php

namespace App\Notifications;

use App\Models\Request as SupplyRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RequestDeclined extends Notification
{
    use Queueable;

    protected SupplyRequest $request;
    protected string $reason;

    /**
     * Create a new notification instance.
     */
    public function __construct(SupplyRequest $request, string $reason)
    {
        $this->request = $request;
        $this->reason = $reason;
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
        $reason = $this->reason;

        // Ensure requestItems are loaded
        if (!$request->relationLoaded('requestItems')) {
            $request->load('requestItems');
        }

        $mail = (new MailMessage)
            ->subject('Request Declined - Supply Office System')
            ->greeting("Hello {$notifiable->name},")
            ->line("Your request has been declined.")
            ->line("**Reason:** {$reason}")
            ->line("**Request Details:**");

        // Add each item to the email
        foreach ($request->requestItems as $requestItem) {
            // Use lazy loading instead of eager loading for polymorphic relationship
            $itemable = $requestItem->itemable;
            $itemName = $itemable ? $itemable->name : 'Unknown Item';
            $mail->line("- Item: {$itemName} (Quantity: {$requestItem->quantity})");
        }

        $mail->line("- Purpose: {$request->purpose}")
            ->line("- Requested Date: " . ($request->needed_date ? $request->needed_date->format('M d, Y') : 'Not specified'))
            ->action('View My Requests', route('faculty.requests.index'))
            ->line('If you have any questions, please contact the supply office.')
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
            'reason' => $this->reason,
        ];
    }
}
