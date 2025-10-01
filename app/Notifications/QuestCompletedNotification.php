<?php

namespace App\Notifications;

use App\Models\Quest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class QuestCompletedNotification extends Notification
{
    use Queueable;

    public $quest;
    public $experienceReward;

    /**
     * Create a new notification instance.
     */
    public function __construct(Quest $quest, int $experienceReward)
    {
        $this->quest = $quest;
        $this->experienceReward = $experienceReward;
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
                    ->subject('Quest Completed - ' . $this->quest->title)
                    ->line('Congratulations! You have completed the quest: ' . $this->quest->title)
                    ->line('Experience gained: ' . $this->experienceReward . ' XP')
                    ->action('View Quests', url('/quests'))
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
            'quest_id' => $this->quest->id,
            'quest_title' => $this->quest->title,
            'experience_reward' => $this->experienceReward,
            'message' => '🎉 Quest completed: ' . $this->quest->title . ' (+' . $this->experienceReward . ' XP)'
        ];
    }
}