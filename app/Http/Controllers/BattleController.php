<?php

namespace App\Http\Controllers;

use App\Models\Battle;
use App\Models\File;
use App\Models\Quiz;
use Illuminate\Http\Request;

class BattleController extends Controller
{
    public function show(Battle $battle)
    {
        $file = File::find($battle->file_id);
        $quizzes = Quiz::where('file_id', $file->id)->get();
        $quizTypes = [
            'multiple_choice' => 'Multiple Choice',
            'true_false' => 'True/False',
            'enumeration' => 'Enumeration',
        ];

        return inertia('Battles/BattleQuiz', [
            'battle' => $battle,
            'file' => $file,
            'quizzes' => $quizzes,
            'quizTypes' => $quizTypes,
        ]);
    }
}
