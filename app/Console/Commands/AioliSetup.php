<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Process\Process;

class AioliSetup extends Command
{
    protected $signature = 'aioli:setup';

    protected $description = 'Runs the necessary setup commands';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('Generating application key...');
        Artisan::call('key:generate');
        $this->info(Artisan::output());

        $this->info('Generating reverb key...');
        Artisan::call('key:generate-reverb');
        $this->info(Artisan::output());

        $this->info('Caching configuration...');
        Artisan::call('config:cache');
        $this->info(Artisan::output());

        $this->info('Running migrations...');
        Artisan::call('migrate', ['--force' => true]);
        $this->info(Artisan::output());

        $this->info('Building frontend assets...');
        $process = new Process(['npm', 'run', 'build']);
        $process->setWorkingDirectory(base_path());
        $process->run(function ($type, $buffer) {
            echo $buffer;
        });

        if ($process->isSuccessful()) {
            $this->info('Frontend assets built successfully.');
        } else {
            $this->error('Error building frontend assets.');
        }

        $this->info('Setup completed successfully.');
    }
}