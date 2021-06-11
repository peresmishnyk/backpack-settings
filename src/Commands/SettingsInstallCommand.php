<?php

namespace Peresmishnyk\BackpackSettings\Commands;

use Illuminate\Console\Command;

class SettingsInstallCommand extends Command
{
    use Traits\PrettyCommandOutput;

    protected $progressBar;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'settings:install
                                {--timeout=300} : How many seconds to allow each process to run.
                                {--debug} : Show process output or not. Useful for debugging.';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install Backpack Settings requirements on dev, publish files';

    /**
     * Execute the console command.
     *
     * @return mixed Command-line output
     */
    public function handle()
    {
        $this->progressBar = $this->output->createProgressBar(3);
        $this->progressBar->minSecondsBetweenRedraws(0);
        $this->progressBar->maxSecondsBetweenRedraws(120);
        $this->progressBar->setRedrawFrequency(1);

        $this->progressBar->start();

        $this->info(' Backpack Settings installation started. Please wait...');
        $this->progressBar->advance();

        $this->line(' Publishing configs, views, js and css files');
        $this->executeArtisanProcess('vendor:publish', [
            '--provider' => 'Peresmishnyk\BackpackSettings\BackpackSettingsServiceProvider',
            '--tag' => 'minimum',
        ]);

        $this->line(" Creating settings table (using Laravel's default migration)");
        $this->executeArtisanProcess('migrate');

        $this->progressBar->finish();
        $this->info(' Backpack Settings installation finished.');
    }
}
