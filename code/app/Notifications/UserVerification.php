<?php

namespace Thaliak\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserVerification extends Notification
{
    use Queueable;

    public function via($notifiable): Array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
                    ->line('Someone (hopefully you!) used this email address for an xiv.world account.')
                    ->line('If this was you, follow the link below to verify your email address.')
                    ->action('Verify Email', url("user/verify/{$notifiable->verification->code}"));
    }

    public function toArray($notifiable): Array
    {
        return [];
    }
}
