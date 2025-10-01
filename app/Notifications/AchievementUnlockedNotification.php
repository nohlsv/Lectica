<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AchievementUnlockedNotification extends Notification
{
    use Queueable;

    public $achievementTitle;
    public $achievementDescription;
    public $achievementIcon;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $title, string $description, string $icon = '🏆')
    {
        $this->achievementTitle = $title;
        $this->achievementDescription = $description;
        $this->achievementIcon = $icon;
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
            ->subject('Achievement Unlocked - ' . $this->achievementTitle)
            ->line('Congratulations! You have unlocked a new achievement.')
            ->line('🏆 ' . $this->achievementTitle)
            ->line($this->achievementDescription)
            ->action('View Profile', url('/profile'))
            ->line('Keep up the great work!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'achievement_title' => $this->achievementTitle,
            'achievement_description' => $this->achievementDescription,
            'achievement_icon' => $this->achievementIcon,
            'message' => $this->achievementIcon . ' Achievement Unlocked: ' . $this->achievementTitle
        ];
    }
}