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
        // Troubleshoot this:
        //Failed to open stream: No such file or directory in /Users/nick/Code/aioli-laravel/artisan on line 9
        
        // $this->info('Installing composer dependencies...');
        // $process = new Process([base_path('/Users/nick/Library/Application Support/Herd/bin//composer'), 'install']);
        // $process->run(function ($type, $buffer) {
        //     echo $buffer;
        // });

        // if ($process->isSuccessful()) {
        //     $this->info('Composer dependencies installed successfully.');
        // } else {
        //     $this->error('Error installing composer dependencies.');
        //     return;
        // }

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
                $this->updateEnv();                
                // Delete .env.example
                // unlink(base_path('.env.example'));
            } else {
                $this->error('.env.example file not found.');
                return;
            }
        } else {
            $this->info('.env file already exists.');
        }

        // $this->info('Updating APP_DOMAIN in .env...');
        // $appDomain = parse_url(config('app.url'), PHP_URL_HOST) ?: 'aioli-laravel.test';
        // file_put_contents(base_path('.env'), preg_replace(
        //     '/^APP_DOMAIN=.*$/m',
        //     'APP_DOMAIN=' . $appDomain,
        //     file_get_contents(base_path('.env'))
        // ));
        // $this->info('APP_DOMAIN updated to ' . $appDomain);

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

    public function updateEnv(){
        $this->info('Updating .env file...');
        $appDomain = basename(base_path()) . '.test';
        file_put_contents(base_path('.env'), preg_replace(
            '/^APP_DOMAIN=.*$/m',
            'APP_DOMAIN=' . $appDomain,
            file_get_contents(base_path('.env'))
        ));
        // update DB_CONNECTION=sqlite to DB_CONNECTION=mysql
        file_put_contents(base_path('.env'), preg_replace(
            '/^DB_CONNECTION=.*$/m',
            'DB_CONNECTION=mysql',
            file_get_contents(base_path('.env'))
        ));
        // update # DB_HOST=127.0.0.1 to DB_HOST=127.0.0.1 (uncomment it)
        file_put_contents(base_path('.env'), preg_replace(
            '/^# DB_HOST=.*$/m',
            'DB_HOST=127.0.0.1',
            file_get_contents(base_path('.env'))
        ));
        // update # DB_PORT=3306 to DB_PORT=3306 (uncomment it)
        file_put_contents(base_path('.env'), preg_replace(
            '/^# DB_PORT=.*$/m',
            'DB_PORT=3306',
            file_get_contents(base_path('.env'))
        ));
        // update # DB_DATABASE=homestead to DB_DATABASE=homestead (uncomment it)
        file_put_contents(base_path('.env'), preg_replace(
            '/^# DB_DATABASE=.*$/m',
            'DB_DATABASE=' . basename(base_path()),
            file_get_contents(base_path('.env'))
        ));
        // update # DB_USERNAME=homestead to DB_USERNAME=homestead (uncomment it)
        file_put_contents(base_path('.env'), preg_replace(
            '/^# DB_USERNAME=.*$/m',
            'DB_USERNAME=homestead',
            file_get_contents(base_path('.env'))
        ));
        // update # DB_PASSWORD=secret to DB_PASSWORD=secret (uncomment it)
        file_put_contents(base_path('.env'), preg_replace(
            '/^# DB_PASSWORD=.*$/m',
            'DB_PASSWORD=secret',
            file_get_contents(base_path('.env'))
        ));
        $this->info('.env file updated successfully.');
    }
}