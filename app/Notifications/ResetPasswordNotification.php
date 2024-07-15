<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public $token;

    /**
     * Create a new notification instance.
     */
    public function __construct($token)
    {
        $this->token = $token;
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
        return (new MailMessage)
            ->subject('Réinitialisation de votre mot de passe')
            ->line('Vous recevez cet e-mail car vous avez effectué une demande de réinitialisation de mot de passe pour votre compte.')
            ->action('Réinitialiser le mot de passe', 'http://evyplus.preprod.inpact.fr/#/password/reset/'. $this->token)
            ->line('Si vous n\'avez pas demandé de réinitialisation de mot de passe, veuillez ignorer cette e-mail');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //

        ];
    }
}
