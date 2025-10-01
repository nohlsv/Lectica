<?php

namespace App\Notifications;

use App\Models\File;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FileVerifiedNotification extends Notification
{
    use Queueable;

    public $file;

    /**
     * Create a new notification instance.
     */
    public function __construct(File $file)
    {
        $this->file = $file;
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
            ->subject('File Verified - ' . $this->file->name)
            ->line('Great news! Your file upload has been verified.')
            ->line('File: ' . $this->file->name)
            ->action('View File', url('/files/' . $this->file->id))
            ->line('Your file is now available to other users. Thank you for contributing!');
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
            'message' => '✅ Your file "' . $this->file->name . '" has been verified!'
        ];
    }
}