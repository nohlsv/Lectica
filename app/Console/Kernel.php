    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Calculate recommendations every 6 hours
        $schedule->command('app:calculate-recommendations')->everySixHours();
    }
