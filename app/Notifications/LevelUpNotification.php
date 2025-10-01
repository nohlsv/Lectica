<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LevelUpNotification extends Notification
{
    use Queueable;

    public $newLevel;
    public $oldLevel;

    /**
     * Create a new notification instance.
     */
    public function __construct(int $newLevel, int $oldLevel)
    {
        $this->newLevel = $newLevel;
        $this->oldLevel = $oldLevel;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('Level Up! - Level ' . $this->newLevel)
                    ->line('Congratulations! You have reached level ' . $this->newLevel . '!')
                    ->line('You have progressed from level ' . $this->oldLevel . ' to level ' . $this->newLevel . '.')
                    ->action('View Profile', url('/profile'))
                    ->line('Keep learning to unlock more achievements!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'new_level' => $this->newLevel,
            'old_level' => $this->oldLevel,
            'message' => '🎊 Level Up! You are now level ' . $this->newLevel
        ];
    }
}