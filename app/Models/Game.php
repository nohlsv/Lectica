<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Game extends Model
{
    /** @use HasFactory<\Database\Factories\GameFactory> */
    use HasFactory;
    protected $fillable = [
        'player_one_id',
        'player_two_id',
        'player_one_score',
        'player_two_score',
        'current_turn',
        'questions',
        'status',
    ];

    protected $casts = [
        'questions' => 'array',
    ];

    public function playerOne(): BelongsTo
    {
        return $this->belongsTo(User::class, 'player_one_id');
    }
    public function playerTwo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'player_two_id');
    }
    public function getPhaseAttribute()
    {
        if (is_null($this->player_two_id)) {
            return 'waiting';
        }
        if ($this->status === 'finished') {
            return 'finished';
        }
        return 'playing';
    }
}
