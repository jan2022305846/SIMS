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

        return (new MailMessage)
            ->subject('Request Declined - Supply Office System')
            ->greeting("Hello {$notifiable->name},")
            ->line("Your request for {$request->quantity} {$request->item->name} has been declined.")
            ->line("**Reason:** {$reason}")
            ->line("**Request Details:**")
            ->line("- Item: {$request->item->name}")
            ->line("- Quantity: {$request->quantity}")
            ->line("- Purpose: {$request->purpose}")
            ->line("- Requested Date: " . ($request->needed_date ? $request->needed_date->format('M d, Y') : 'Not specified'))
            ->action('View My Requests', route('faculty.requests.index'))
            ->line('If you have any questions, please contact the supply office.')
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
            'reason' => $this->reason,
        ];
    }
}
