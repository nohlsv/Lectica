<?php

namespace App\Notifications;

use App\Models\Battle;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BattleVictoryNotification extends Notification
{
    use Queueable;

    public $battle;
    public $experienceGained;

    /**
     * Create a new notification instance.
     */
    public function __construct(Battle $battle, int $experienceGained)
    {
        $this->battle = $battle;
        $this->experienceGained = $experienceGained;
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
                    ->subject('Victory! Battle Won')
                    ->line('Congratulations! You won a battle!')
                    ->line('Experience gained: ' . $this->experienceGained . ' XP')
                    ->action('View Battles', url('/battles'))
                    ->line('Keep battling to improve your skills!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'battle_id' => $this->battle->id,
            'experience_gained' => $this->experienceGained,
            'message' => '⚔️ Victory! You won a battle and gained ' . $this->experienceGained . ' XP'
        ];
    }
}