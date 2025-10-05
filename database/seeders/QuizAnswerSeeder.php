<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Quiz;
use App\Models\QuizAnswer;
use App\Models\Battle;
use App\Models\MultiplayerGame;
use App\Models\File;
use Carbon\Carbon;

class QuizAnswerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing data
        $users = User::all();
        $quizzes = Quiz::with('file')
            ->whereNotNull('answers')
            ->get();
        $battles = Battle::all();
        $multiplayerGames = MultiplayerGame::all();

        $this->command->info("Found {$quizzes->count()} quizzes with answers");

        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please seed users first.');
            return;
        }

        if ($quizzes->isEmpty()) {
            $this->command->warn('No quizzes with answers found. Please ensure quizzes have answers values.');
            return;
        }

        $this->command->info("Found {$battles->count()} battles and {$multiplayerGames->count()} multiplayer games for context");
        $this->command->info('Creating quiz answer records...');

        // Sample answers for different quiz types
        $sampleAnswers = [
            'multiple_choice' => ['A', 'B', 'C', 'D'],
            'true_false' => ['True', 'False'],
            'enumeration' => [
                'Paris, London, Berlin',
                'Red, Blue, Green',
                'Apple, Orange, Banana',
                'Dog, Cat, Bird',
                'Math, Science, English'
            ]
        ];

        $createdAnswers = 0;

        foreach ($users as $user) {
            // Create 15-30 quiz answers per user
            $answerCount = rand(15, 30);
            $this->command->info("Creating {$answerCount} answers for user {$user->id}");
            
            for ($i = 0; $i < $answerCount; $i++) {
                $quiz = $quizzes->random();
                
                // Debug: Check if quiz has answers
                if (empty($quiz->answers)) {
                    $this->command->warn("Quiz {$quiz->id} has no answers, skipping");
                    continue;
                }
                
                // Determine context (70% battle, 30% multiplayer)
                $contextType = rand(1, 10) <= 7 ? 'battle' : 'multiplayer';
                $contextId = null;
                
                if ($contextType === 'battle' && $battles->isNotEmpty()) {
                    $contextId = $battles->random()->id;
                } elseif ($contextType === 'multiplayer' && $multiplayerGames->isNotEmpty()) {
                    $contextId = $multiplayerGames->random()->id;
                } else {
                    // Fallback to battle if no multiplayer games exist
                    $contextType = 'battle';
                    $contextId = $battles->isNotEmpty() ? $battles->random()->id : null;
                }

                // Allow quiz answers without context (standalone practice)
                if (!$contextId) {
                    $contextType = null;
                }

                // Determine if answer is correct (70% chance of being correct for more realistic data)
                $isCorrect = rand(1, 10) <= 7;
                
                // Generate user answer based on quiz type
                if ($isCorrect) {
                    // Use the correct answer from the answers array
                    $correctAnswers = is_array($quiz->answers) ? $quiz->answers : [$quiz->answers];
                    $userAnswer = $correctAnswers[0]; // Use first correct answer
                } else {
                    // Generate an incorrect answer
                    $userAnswer = $this->generateUserAnswer($quiz, $sampleAnswers);
                }
                
                // Ensure we have a valid answer
                if (empty($userAnswer) || $userAnswer === null) {
                    $correctAnswers = is_array($quiz->answers) ? $quiz->answers : [$quiz->answers];
                    $userAnswer = $isCorrect ? $correctAnswers[0] : 'Sample incorrect answer';
                }



                // Random timestamp within the last 30 days
                $answeredAt = Carbon::now()->subDays(rand(0, 30))->subHours(rand(0, 23))->subMinutes(rand(0, 59));

                try {
                    QuizAnswer::create([
                        'user_id' => $user->id,
                        'quiz_id' => $quiz->id,
                        'user_answer' => $userAnswer,
                        'is_correct' => $isCorrect,
                        'context_type' => $contextType,
                        'context_id' => $contextId,
                        'answered_at' => $answeredAt,
                    ]);
                    
                    $createdAnswers++;
                } catch (\Exception $e) {
                    $this->command->warn("Failed to create answer for quiz {$quiz->id}: " . $e->getMessage());
                    continue;
                }
            }
        }

        $this->command->info("Created {$createdAnswers} quiz answer records successfully!");
        
        // Show some statistics
        $totalAnswers = QuizAnswer::count();
        $correctAnswers = QuizAnswer::where('is_correct', true)->count();
        $battleAnswers = QuizAnswer::where('context_type', 'battle')->count();
        $multiplayerAnswers = QuizAnswer::where('context_type', 'multiplayer')->count();
        $accuracy = $totalAnswers > 0 ? round(($correctAnswers / $totalAnswers) * 100, 2) : 0;

        $this->command->info("Quiz Answer Statistics:");
        $this->command->line("- Total Answers: {$totalAnswers}");
        $this->command->line("- Correct Answers: {$correctAnswers}");
        $this->command->line("- Overall Accuracy: {$accuracy}%");
        $this->command->line("- Battle Answers: {$battleAnswers}");
        $this->command->line("- Multiplayer Answers: {$multiplayerAnswers}");

        // Show file distribution
        $fileStats = QuizAnswer::with('quiz.file')
            ->get()
            ->groupBy('quiz.file.name')
            ->map(function ($answers) {
                return [
                    'count' => $answers->count(),
                    'correct' => $answers->where('is_correct', true)->count(),
                    'accuracy' => round(($answers->where('is_correct', true)->count() / $answers->count()) * 100, 2)
                ];
            });

        $this->command->info("\nFile Distribution:");
        foreach ($fileStats as $fileName => $stats) {
            $this->command->line("- {$fileName}: {$stats['count']} answers ({$stats['accuracy']}% accuracy)");
        }
    }

    /**
     * Generate a user answer based on quiz type (for incorrect answers)
     */
    private function generateUserAnswer($quiz, $sampleAnswers): string
    {
        $correctAnswers = is_array($quiz->answers) ? $quiz->answers : [$quiz->answers];
        $correctAnswer = $correctAnswers[0];
        
        switch ($quiz->type) {
            case 'multiple_choice':
                // If quiz has options, use one of them (but not the correct one)
                if ($quiz->options && is_array($quiz->options)) {
                    $options = array_values($quiz->options);
                    $incorrectOptions = array_filter($options, function($option) use ($correctAnswers) {
                        return !in_array($option, $correctAnswers);
                    });
                    
                    if (!empty($incorrectOptions)) {
                        return $incorrectOptions[array_rand($incorrectOptions)];
                    }
                }
                // Fallback to sample answers
                $samples = $sampleAnswers['multiple_choice'];
                return $samples[array_rand($samples)];

            case 'true_false':
                // Return the opposite of the correct answer
                return $correctAnswer === 'True' ? 'False' : 'True';

            case 'enumeration':
                // Use sample enumeration answers
                return $sampleAnswers['enumeration'][array_rand($sampleAnswers['enumeration'])];

            default:
                // For unknown types, return a generic answer
                return 'Incorrect sample answer';
        }
    }
}