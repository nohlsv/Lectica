<?php

namespace App\Notifications;

use App\Models\File;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class ContentGenerationComplete extends Notification
{
    use Queueable;

    public function __construct(
        private File $file,
        private array $results,
        private array $errors
    ) {}

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    private function getMessage()
    {
        $status = empty($this->errors) ? 'completed successfully' : 'completed with some errors';
        return "Content generation $status";
    }

    private function getNotificationData()
    {
        return [
            'message' => $this->getMessage(),
            'file_id' => $this->file->id,
            'file_name' => $this->file->name,
            'error_count' => count($this->errors)
        ];
    }

    public function toArray($notifiable)
    {
        return $this->getNotificationData();
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage($this->getNotificationData());
    }

    public function toDatabase($notifiable)
    {
        $data = $this->getNotificationData();
        $data['read_at'] = null;
        return $data;
    }
}