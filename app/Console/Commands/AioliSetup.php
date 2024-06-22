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
        $this->info('Installing composer dependencies...');
        $process = new Process(['composer', 'install']);
        $process->run(function ($type, $buffer) {
            echo $buffer;
        });

        if ($process->isSuccessful()) {
            $this->info('Composer dependencies installed successfully.');
        } else {
            $this->error('Error installing composer dependencies.');
            return;
        }

        $this->info('Installing npm dependencies...');
        $process = new Process(['npm', 'install']);
        $process->run(function ($type, $buffer) {
            echo $buffer;
        });

        if ($process->isSuccessful()) {
            $this->info('NPM dependencies installed successfully.');
        } else {
            $this->error('Error installing npm dependencies.');
            return;
        }

        $this->info('Renaming .env.example to .env...');
        if (!file_exists(base_path('.env'))) {
            if (file_exists(base_path('.env.example'))) {
                rename(base_path('.env.example'), base_path('.env'));
                $this->info('.env file created successfully.');
            } else {
                $this->error('.env.example file not found.');
                return;
            }
        } else {
            $this->info('.env file already exists.');
        }

        $this->info('Updating APP_DOMAIN in .env...');
        $appDomain = parse_url(config('app.url'), PHP_URL_HOST) ?: 'aioli-laravel.test';
        file_put_contents(base_path('.env'), preg_replace(
            '/^APP_DOMAIN=.*$/m',
            'APP_DOMAIN=' . $appDomain,
            file_get_contents(base_path('.env'))
        ));
        $this->info('APP_DOMAIN updated to ' . $appDomain);

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