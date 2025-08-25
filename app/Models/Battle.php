<?php

namespace App\Models;

use App\Monster;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Battle extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'user_id', 'monster_id', 'file_id',
        'status', 'player_hp', 'monster_hp',
        'correct_answers', 'total_questions'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function monster(): BelongsTo
    {
        return $this->belongsTo(Monster::class)->withDefault(function ($monster, $battle) {
            $monsterData = \App\Monster::find($battle->monster_id);
            if ($monsterData) {
                $monster->id = $monsterData->id;
                $monster->name = $monsterData->name;
                $monster->hp = $monsterData->hp;
                $monster->attack = $monsterData->attack;
                $monster->defense = $monsterData->defense;
                $monster->image_path = $monsterData->image_path;
                $monster->difficulty = $monsterData->difficulty;
            }
            return $monster;
        });
    }

    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }

}
