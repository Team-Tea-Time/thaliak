<?php

namespace Thaliak\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordReset extends Notification
{
    use Queueable;

    protected $token; // String

    public function __construct(String $token)
    {
        $this->token = $token;
    }

    public function via($notifiable): Array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
                    ->line('Someone (hopefully you!) has requested a password reset for the xiv.world account associated with this email address.')
                    ->line('If this was you, follow the link below to set a new password.')
                    ->action('Reset Password', url("user/password-reset/{$this->token}"));
    }

    public function toArray($notifiable): Array
    {
        return [];
    }
}
