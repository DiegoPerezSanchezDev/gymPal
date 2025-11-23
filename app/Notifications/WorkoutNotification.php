<?php

namespace App\Notifications;

use App\Models\User;
use App\Models\Workout;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WorkoutNotification extends Notification
{
    use Queueable;

    protected $actionUser;
    protected $workout;
    protected $type; // 'saved' or 'cloned'

    /**
     * Create a new notification instance.
     */
    public function __construct(User $actionUser, Workout $workout, string $type)
    {
        $this->actionUser = $actionUser;
        $this->workout = $workout;
        $this->type = $type;
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
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'workout_' . $this->type, // workout_saved, workout_cloned
            'user_id' => $this->actionUser->id,
            'user_name' => $this->actionUser->name,
            'user_avatar' => $this->actionUser->profile_picture_url,
            'workout_id' => $this->workout->id,
            'workout_name' => $this->workout->name,
            'message' => $this->type === 'saved' 
                ? "guardó tu rutina" 
                : "clonó tu rutina",
        ];
    }
}
