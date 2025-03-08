<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\Process\Process;
use App\Models\User;

class AioliSetup extends Command
{
    protected $signature = 'aioli:setup';

    protected $description = 'Runs the necessary setup commands and creates an admin user';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
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
            } else {
                $this->error('.env.example file not found.');
                return;
            }
        } else {
            $this->info('.env file already exists.');
        }

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

        // Create admin user
        $this->createAdminUser();

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
            return;
        }

        // Open browser only if --no-browser option is not set
        // if (!$this->option('no-browser')) {
        //     $this->info('Opening browser...');
        //     $appDomain = basename(base_path()) . '.test';
        //     $process = new Process(['open', 'http://' . $appDomain]);
        //     $process->run();
        // }

        $this->info('Setup completed successfully.');
    }

    /**
     * Create admin user with predefined credentials
     *
     * @return void
     */
    protected function createAdminUser()
    {
        $this->info('Creating admin user...');
        
        // Email and password for the admin user
        $email = 'admin@aioli.com';
        $password = 'WJjtLXciTd&aI!8d'; // Strong password for security
        
        // Check if user already exists
        $existingUser = User::where('email', $email)->first();
        
        if ($existingUser) {
            $this->info("Admin user already exists with email: {$email}");
            return;
        }
        
        try {
            // Create a new admin user
            $user = User::create([
                'name' => 'Admin',
                'email' => $email,
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ]);
            
            // Add any admin role/permissions if your app uses them
            // $user->assignRole('admin'); // Uncomment if using Spatie permissions
            
            $this->info("Admin user created successfully with:");
            $this->info("Email: {$email}");
            $this->info("Password: {$password}");
            
            // Write the credentials to a local file for reference
            $credentialsFile = base_path('admin-credentials.txt');
            file_put_contents($credentialsFile, "Admin Credentials\n----------------\nEmail: {$email}\nPassword: {$password}\n");
            $this->info("Admin credentials saved to: {$credentialsFile}");
            
        } catch (\Exception $e) {
            $this->error("Failed to create admin user: " . $e->getMessage());
        }
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
            'DB_USERNAME=root',
            file_get_contents(base_path('.env'))
        ));
        // update # DB_PASSWORD=secret to DB_PASSWORD=secret (uncomment it)
        file_put_contents(base_path('.env'), preg_replace(
            '/^# DB_PASSWORD=.*$/m',
            'DB_PASSWORD=',
            file_get_contents(base_path('.env'))
        ));
        $this->info('.env file updated successfully.');
    }
}