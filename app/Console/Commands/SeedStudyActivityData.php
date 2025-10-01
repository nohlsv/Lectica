<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\UserStudyActivity;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SeedStudyActivityData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'study:seed-activity {--user-id=1 : The user ID to seed data for} {--days=90 : Number of days to seed} {--clear : Clear existing data first}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed sample study activity data for testing the heatmap';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = (int) $this->option('user-id');
        $days = (int) $this->option('days');

        $user = User::find($userId);
        if (!$user) {
            $this->error("User with ID {$userId} not found.");
            return Command::FAILURE;
        }

        // Clear existing data if requested
        if ($this->option('clear')) {
            $this->info("Clearing existing activity data for user: {$user->name}");
            UserStudyActivity::where('user_id', $userId)->delete();
        }

        $this->info("Seeding {$days} days of study activity for user: {$user->name}");

        $progressBar = $this->output->createProgressBar($days);
        $progressBar->start();

        $currentDate = Carbon::now()->subDays($days);
        $activitiesCreated = 0;

        // Create realistic study patterns with guaranteed streaks
        $inStreak = false;
        $streakLength = 0;
        $targetStreakLength = 0;
        $streakCooldown = 0; // Days after streak before starting another

        for ($i = 0; $i < $days; $i++) {
            $shouldStudy = false;
            
            if ($streakCooldown > 0) {
                $streakCooldown--;
                // Lower probability during cooldown
                $shouldStudy = rand(1, 100) <= 30;
            } elseif (!$inStreak && rand(1, 100) <= 20) { // 20% chance to start a streak
                $inStreak = true;
                $targetStreakLength = rand(7, 25); // 7-25 day streaks
                $streakLength = 0;
                $shouldStudy = true; // Always start streak
                $this->line("\n  Starting streak of {$targetStreakLength} days at {$currentDate->format('Y-m-d')}");
            } elseif ($inStreak) {
                // GUARANTEE every day during streak
                $shouldStudy = true;
                $streakLength++;
                if ($streakLength >= $targetStreakLength) {
                    $inStreak = false;
                    $streakCooldown = rand(3, 10); // 3-10 day break after streak
                    $this->line("  Ending streak at {$currentDate->format('Y-m-d')} (length: {$streakLength})");
                }
            } else {
                // Regular random probability outside of streaks
                $shouldStudy = rand(1, 100) <= 70;
            }

            if ($shouldStudy) {
                $intensity = $this->getRandomIntensity();
                
                UserStudyActivity::updateOrCreate(
                    [
                        'user_id' => $userId,
                        'study_date' => $currentDate->copy(),
                    ],
                    $this->generateActivityData($intensity)
                );
                
                $activitiesCreated++;
            }

            $currentDate->addDay();
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->line('');
        $this->info("Successfully created {$activitiesCreated} study activity records!");

        return Command::SUCCESS;
    }

    /**
     * Get a random intensity level based on realistic patterns.
     */
    private function getRandomIntensity(): int
    {
        $random = rand(1, 100);
        
        if ($random <= 40) return 1; // Light activity (40%)
        if ($random <= 70) return 2; // Medium activity (30%)
        if ($random <= 90) return 3; // High activity (20%)
        return 4; // Very high activity (10%)
    }

    /**
     * Generate activity data based on intensity level.
     */
    private function generateActivityData(int $intensity): array
    {
        switch ($intensity) {
            case 1: // Light activity
                return [
                    'quizzes_completed' => rand(0, 1),
                    'questions_answered' => rand(5, 15),
                    'correct_answers' => rand(3, 12),
                    'points_earned' => rand(10, 50),
                    'time_studied' => rand(5, 20),
                    'battles_participated' => rand(0, 1),
                    'flashcards_reviewed' => rand(0, 10),
                ];
                
            case 2: // Medium activity
                return [
                    'quizzes_completed' => rand(1, 3),
                    'questions_answered' => rand(15, 40),
                    'correct_answers' => rand(10, 30),
                    'points_earned' => rand(50, 150),
                    'time_studied' => rand(20, 45),
                    'battles_participated' => rand(0, 2),
                    'flashcards_reviewed' => rand(5, 25),
                ];
                
            case 3: // High activity
                return [
                    'quizzes_completed' => rand(3, 6),
                    'questions_answered' => rand(40, 80),
                    'correct_answers' => rand(25, 60),
                    'points_earned' => rand(150, 400),
                    'time_studied' => rand(45, 90),
                    'battles_participated' => rand(1, 3),
                    'flashcards_reviewed' => rand(20, 50),
                ];
                
            case 4: // Very high activity
                return [
                    'quizzes_completed' => rand(6, 12),
                    'questions_answered' => rand(80, 150),
                    'correct_answers' => rand(50, 120),
                    'points_earned' => rand(400, 800),
                    'time_studied' => rand(90, 180),
                    'battles_participated' => rand(2, 5),
                    'flashcards_reviewed' => rand(40, 100),
                ];
                
            default:
                return [];
        }
    }
}
