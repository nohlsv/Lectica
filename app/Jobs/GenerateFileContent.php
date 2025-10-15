<?php

namespace App\Jobs;

use App\Models\File;
use App\Http\Controllers\FileController;
use App\Notifications\ContentGenerationComplete;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateFileContent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $fileController;

    public function __construct(
        private File $file,
        private array $types,
        private array $counts
    ) {
        $this->fileController = app(FileController::class);
    }

    public function handle()
    {
        $results = [];
        $errors = [];
        
        try {
            Log::info('Starting content generation job', [
                'file_id' => $this->file->id,
                'types' => $this->types,
                'counts' => $this->counts
            ]);

            $this->file->update(['generation_status' => 'processing']);
            
            foreach ($this->types as $type) {
                Log::info('Generating content for type', ['type' => $type]);
                
                try {
                    $count = $this->counts[$type] ?? 5;
                    
                    $result = match ($type) {
                        'flashcards' => $this->fileController->generateFlashcardsContent($this->file, $count),
                        'multiple_choice' => $this->fileController->generateMultipleChoiceContent($this->file, $count),
                        'enumeration' => $this->fileController->generateEnumerationContent($this->file, $count),
                        'true_false' => $this->fileController->generateTrueFalseContent($this->file, $count),
                        default => ['success' => false, 'error' => "Unknown content type: {$type}"]
                    };

                    if (!empty($result['error'])) {
                        Log::error('Error generating content', [
                            'type' => $type,
                            'error' => $result['error']
                        ]);
                        $errors[$type] = $result['error'];
                    } else {
                        Log::info('Successfully generated content', [
                            'type' => $type,
                            'count' => $count
                        ]);
                        $results[$type] = true;
                    }
                } catch (\Exception $e) {
                    Log::error('Exception while generating content', [
                        'type' => $type,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    $errors[$type] = $e->getMessage();
                }
            }

            $status = empty($errors) ? 'completed' : 'completed_with_errors';
            $this->file->update([
                'generation_status' => $status,
                'generation_errors' => !empty($errors) ? $errors : null
            ]);

            Log::info('Content generation completed', [
                'file_id' => $this->file->id,
                'status' => $status,
                'results' => $results,
                'errors' => $errors
            ]);

            // Notify the user
            $this->file->user->notify(new ContentGenerationComplete($this->file, $results, $errors));

        } catch (\Exception $e) {
            Log::error('Fatal error in content generation job', [
                'file_id' => $this->file->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->file->update([
                'generation_status' => 'failed',
                'generation_errors' => ['system' => $e->getMessage()]
            ]);

            $this->file->user->notify(new ContentGenerationComplete($this->file, [], ['system' => $e->getMessage()]));
        }
    }
}