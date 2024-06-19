<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateReverbKeys extends Command
{
    protected $signature = 'key:generate-reverb';

    protected $description = 'Generate and set REVERB_APP_ID, REVERB_APP_KEY, and REVERB_APP_SECRET in .env file';

    public function handle()
    {
        // Generate the keys
        $appId = random_int(100000, 999999);
        $appKey = Str::random(20);
        $appSecret = Str::random(20);

        // Update the .env file
        $this->updateEnvFile('REVERB_APP_ID', $appId);
        $this->updateEnvFile('REVERB_APP_KEY', $appKey);
        $this->updateEnvFile('REVERB_APP_SECRET', $appSecret);

        $this->info('Reverb keys generated successfully!');
    }

    protected function updateEnvFile($key, $value)
    {
        $path = base_path('.env');

        if (file_exists($path)) {
            $envFileContent = file_get_contents($path);

            if (strpos($envFileContent, "{$key}=") !== false) {
                $envFileContent = preg_replace("/^{$key}=.*$/m", "{$key}={$value}", $envFileContent);
            } else {
                $envFileContent .= "\n{$key}={$value}";
            }

            file_put_contents($path, $envFileContent);
        }
    }
}