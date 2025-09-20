<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SetPasswordNotification extends Notification
{
    use Queueable;

    protected $token;
    protected $isInitialSetup;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $token, bool $isInitialSetup = true)
    {
        $this->token = $token;
        $this->isInitialSetup = $isInitialSetup;
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
        $url = url('/password/set/' . $this->token);

        if ($this->isInitialSetup) {
            return (new MailMessage)
                ->subject('Welcome to SIMS - Set Your Password')
                ->greeting('Hi ' . $notifiable->name . ',')
                ->line('An account has been created for you on the USTP Panaon Supply Office Inventory Management System (SIMS).')
                ->line('Please click the button below to set your password and activate your account.')
                ->action('Set My Password', $url)
                ->line('This link will expire in 24 hours for your security.')
                ->line('If you did not request this account setup, please ignore this email.')
                ->salutation('Best regards, SIMS Team');
        } else {
            return (new MailMessage)
                ->subject('SIMS - Password Reset Request')
                ->greeting('Hi ' . $notifiable->name . ',')
                ->line('You requested to reset your password for the USTP Panaon Supply Office Inventory Management System (SIMS).')
                ->line('Please click the button below to set a new password.')
                ->action('Reset My Password', $url)
                ->line('This link will expire in 24 hours for your security.')
                ->line('If you did not request this password reset, please ignore this email.')
                ->salutation('Best regards, SIMS Team');
        }
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'token' => $this->token,
            'is_initial_setup' => $this->isInitialSetup,
        ];
    }
}
