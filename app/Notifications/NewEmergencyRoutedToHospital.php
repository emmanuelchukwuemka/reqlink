<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewEmergencyRoutedToHospital extends Notification
{
    use Queueable;

    public $emergency;

    public function __construct($emergency)
    {
        $this->emergency = $emergency;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $patientName = $this->emergency->user->name ?? 'A patient';

        return (new MailMessage)
            ->subject('New Incoming Emergency — ResQLink')
            ->line("{$patientName} is being routed to your facility.")
            ->line('Priority: ' . $this->emergency->priority)
            ->line('Log in to your ResQLink dashboard to view details and accept the patient.')
            ->action('Open Dashboard', url('/dashboard'));
    }
}
