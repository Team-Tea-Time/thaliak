<?php

namespace Thaliak\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Thaliak\User;

class UserConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var User
     */
    private $user;

    /**
     * Create a new user confirmation mail instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return Mailable
     */
    public function build()
    {
        return $this->header('Confirm your eorzea.info account')
                    ->line('Someone (hopefully you!) created an eorzea.info account using this email address.')
                    ->line('If this was you, follow the link below to confirm your email address.')
                    ->action('Confirm Email', url('/'));
    }
}
