<?php

namespace App\Console\Commands;

use App\Models\Quest;
use Illuminate\Console\Command;

class CleanupInvalidQuests extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'quests:cleanup-invalid {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove quests with invalid categories that are not supported by QuestService';

    /**
     * Valid quest categories as defined in QuestService
     */
    private array $validCategories = [
        'practice_quiz',
        'practice_flashcard', 
        'battle_start',
        'battle_win',
        'battle_questions',
        'multiplayer_create',
        'multiplayer_join',
        'multiplayer_win',
        'multiplayer_questions',
        'file_create',
        'collection_create',
        'quiz_generate',
        'daily_login',
        'activity_streak',
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for quests with invalid categories...');

        // Find quests with invalid categories
        $invalidQuests = Quest::whereNotIn('category', $this->validCategories)->get();

        if ($invalidQuests->isEmpty()) {
            $this->info('✅ No invalid quests found. All quest categories are valid.');
            return 0;
        }

        $this->warn("Found {$invalidQuests->count()} quest(s) with invalid categories:");
        
        // Display invalid quests in a table
        $tableData = [];
        foreach ($invalidQuests as $quest) {
            $tableData[] = [
                'ID' => $quest->id,
                'Title' => $quest->title,
                'Category' => $quest->category,
                'Type' => $quest->type,
                'Active' => $quest->is_active ? 'Yes' : 'No',
                'User Assignments' => $quest->users()->count(),
            ];
        }

        $this->table([
            'ID', 'Title', 'Category', 'Type', 'Active', 'User Assignments'
        ], $tableData);

        if ($this->option('dry-run')) {
            $this->info('🔍 Dry run mode: No changes were made.');
            $this->info('To actually remove these quests, run without --dry-run option.');
            return 0;
        }

        // Confirm deletion
        if (!$this->confirm('Do you want to delete these invalid quests?')) {
            $this->info('Operation cancelled.');
            return 0;
        }

        // Delete invalid quests and their user assignments
        $deletedCount = 0;
        $userAssignmentsDeleted = 0;

        foreach ($invalidQuests as $quest) {
            // Count user assignments before deletion
            $assignmentCount = $quest->users()->count();
            $userAssignmentsDeleted += $assignmentCount;
            
            // Delete the quest (this will also delete pivot table entries due to cascade)
            $quest->delete();
            $deletedCount++;
            
            $this->info("✅ Deleted quest: '{$quest->title}' (Category: {$quest->category}) - {$assignmentCount} user assignments removed");
        }

        $this->info("🗑️  Successfully deleted {$deletedCount} invalid quest(s)");
        $this->info("🗑️  Removed {$userAssignmentsDeleted} user quest assignment(s)");
        
        // Show valid categories for reference
        $this->info("\n📋 Valid quest categories:");
        foreach ($this->validCategories as $category) {
            $this->line("   • {$category}");
        }

        return 0;
    }
}
