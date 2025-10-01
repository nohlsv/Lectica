<?php

namespace App\Console\Commands;

use App\Models\Collection;
use Illuminate\Console\Command;

class SyncCollectionCounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'collections:sync-counts {--dry-run : Show what would be updated without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync file counts and question counts for all collections';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->info('Running in dry-run mode - no changes will be made');
        }

        $this->info('Syncing collection counts...');
        
        $collections = Collection::all();
        $updatedCount = 0;
        
        $progressBar = $this->output->createProgressBar($collections->count());
        $progressBar->start();

        foreach ($collections as $collection) {
            // Calculate current counts
            $files = $collection->files()->with('quizzes')->get();
            $actualFileCount = $files->count();
            $actualQuestionCount = $files->sum(function ($file) {
                return $file->quizzes->count();
            });

            // Check if counts need updating
            $needsUpdate = $collection->file_count !== $actualFileCount || 
                          $collection->total_questions !== $actualQuestionCount;

            if ($needsUpdate) {
                if ($dryRun) {
                    $this->line('');
                    $this->warn("Collection '{$collection->name}' (ID: {$collection->id}) needs updating:");
                    $this->line("  File Count: {$collection->file_count} → {$actualFileCount}");
                    $this->line("  Question Count: {$collection->total_questions} → {$actualQuestionCount}");
                } else {
                    $collection->update([
                        'file_count' => $actualFileCount,
                        'total_questions' => $actualQuestionCount,
                    ]);
                }
                $updatedCount++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->line('');

        if ($dryRun) {
            $this->info("Found {$updatedCount} collections that need updating.");
            $this->info('Run without --dry-run to apply changes.');
        } else {
            $this->info("Successfully updated {$updatedCount} collections.");
        }

        return Command::SUCCESS;
    }
}
