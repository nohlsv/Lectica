<?php

namespace App\Notifications;

use App\Models\File;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FileDeniedNotification extends Notification
{
    use Queueable;

    public $file;
    public $denialReason;

    /**
     * Create a new notification instance.
     */
    public function __construct(File $file, string $denialReason)
    {
        $this->file = $file;
        $this->denialReason = $denialReason;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('File Upload Denied - ' . $this->file->name)
            ->line('Your file upload "' . $this->file->name . '" has been denied.')
            ->line('Reason: ' . $this->denialReason)
            ->action('View My Files', url('/files'))
            ->line('You can re-upload the file after addressing the issues mentioned above.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'file_id' => $this->file->id,
            'file_name' => $this->file->name,
            'denial_reason' => $this->denialReason,
            'message' => 'Your file "' . $this->file->name . '" has been denied: ' . $this->denialReason
        ];
    }
}
