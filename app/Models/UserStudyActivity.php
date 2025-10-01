<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class UserStudyActivity extends Model
{
    protected $fillable = [
        'user_id',
        'study_date',
        'quizzes_completed',
        'questions_answered',
        'correct_answers',
        'points_earned',
        'time_studied',
        'battles_participated',
        'flashcards_reviewed',
    ];

    protected $casts = [
        'study_date' => 'date',
    ];

    /**
     * Get the user that owns this activity record.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the XP earned (alias for points_earned for frontend consistency).
     */
    public function getXpEarnedAttribute(): int
    {
        return $this->points_earned;
    }

    /**
     * Append custom attributes to the model's array form.
     */
    protected $appends = ['xp_earned'];

    /**
     * Record or update activity for a user on a specific date.
     */
    public static function recordActivity(
        int $userId,
        ?Carbon $date = null,
        array $activities = []
    ): self {
        $date = $date ?? today();
        
        $activity = self::updateOrCreate(
            [
                'user_id' => $userId,
                'study_date' => $date,
            ],
            [
                'quizzes_completed' => ($activities['quizzes_completed'] ?? 0),
                'questions_answered' => ($activities['questions_answered'] ?? 0),
                'correct_answers' => ($activities['correct_answers'] ?? 0),
                'points_earned' => ($activities['points_earned'] ?? 0),
                'time_studied' => ($activities['time_studied'] ?? 0),
                'battles_participated' => ($activities['battles_participated'] ?? 0),
                'flashcards_reviewed' => ($activities['flashcards_reviewed'] ?? 0),
            ]
        );

        return $activity;
    }

    /**
     * Increment activity counters for a user today.
     */
    public static function incrementActivity(
        int $userId,
        array $increments = []
    ): self {
        $activity = self::firstOrCreate(
            [
                'user_id' => $userId,
                'study_date' => today(),
            ]
        );

        foreach ($increments as $field => $value) {
            if (in_array($field, ['quizzes_completed', 'questions_answered', 'correct_answers', 'points_earned', 'time_studied', 'battles_participated', 'flashcards_reviewed'])) {
                $activity->increment($field, $value);
            }
        }

        return $activity->fresh();
    }
}
