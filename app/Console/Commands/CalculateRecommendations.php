<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\FileRecommendationService;
use Illuminate\Console\Command;

class CalculateRecommendations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:calculate-recommendations {--user= : Optional user ID to calculate for specific user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pre-calculate file recommendations for users';

    /**
     * Execute the console command.
     */
    public function handle(FileRecommendationService $recommendationService): int
    {
        $userId = $this->option('user');

        if ($userId) {
            $user = User::find($userId);
            if (!$user) {
                $this->error("User with ID {$userId} not found.");
                return 1;
            }

            $this->info("Calculating recommendations for user {$user->name}...");
            $recommendationService->getRecommendations($user);
            $this->info("Recommendations calculated and cached.");

            return 0;
        }

        $userCount = User::count();
        $bar = $this->output->createProgressBar($userCount);
        $bar->start();

        User::chunk(100, function ($users) use ($recommendationService, $bar) {
            foreach ($users as $user) {
                $recommendationService->getRecommendations($user);
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();
        $this->info("Recommendations calculated and cached for all users.");

        return 0;
    }
}
