<?php

namespace App\Console\Commands;

use App\Services\GameTimerService;
use Illuminate\Console\Command;

class ProcessGameTimers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'timers:process {--loop : Run continuously in a loop}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process game timers and handle timeouts';

    /**
     * Execute the console command.
     */
    public function handle(GameTimerService $timerService): int
    {
        if ($this->option('loop')) {
            $this->info('Starting continuous timer processing...');
            
            while (true) {
                $this->processTimers($timerService);
                sleep(1); // Check every second
            }
        } else {
            $this->processTimers($timerService);
        }

        return Command::SUCCESS;
    }

    private function processTimers(GameTimerService $timerService): void
    {
        try {
            $timerService->processAllTimers();
        } catch (\Exception $e) {
            $this->error('Error processing timers: ' . $e->getMessage());
            \Log::error('Timer processing error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
