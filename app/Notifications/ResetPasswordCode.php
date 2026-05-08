<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

class ResetPasswordCode extends Notification
{
    use Queueable;

    public $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject(Lang::get('Reset Password Code'))
            ->line(Lang::get('You are receiving this email because we received a password reset request for your account.'))
            ->line(Lang::get('Your password reset code is:'))
            ->view('emails.password-reset-code', [
                'code' => $this->token,
                'name' => $notifiable->name
            ])
            ->line(Lang::get('This password reset code will expire in :count minutes.', ['count' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire')]))
            ->line(Lang::get('If you did not request a password reset, no further action is required.'));
    }
}
