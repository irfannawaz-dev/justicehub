<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CaseNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string  $title      = 'Case Update',
        public string  $message    = '',
        public ?string $actionText = null,
        public ?string $actionUrl  = null,
        public string  $type       = 'info',  // info | assigned | updated | approved | rejected | resolved | sla
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject($this->title)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line($this->message);

        if ($this->actionText && $this->actionUrl) {
            $mail->action($this->actionText, $this->actionUrl);
        }

        return $mail->line('— Justice Hub CMS');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title'      => $this->title,
            'message'    => $this->message,
            'action_url' => $this->actionUrl,
            'type'       => $this->type,
        ];
    }
}
