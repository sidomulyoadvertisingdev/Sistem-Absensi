<?php

namespace App\Console\Commands;

use Google\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Process;

class AbsensiAuthorizeGoogleDrive extends Command
{
    protected $signature = 'absensi:authorize';

    protected $description = 'Generate Google Drive refresh token untuk backup foto absensi';

    public function handle(): int
    {
        $clientId = env('GOOGLE_DRIVE_CLIENT_ID');
        $clientSecret = env('GOOGLE_DRIVE_CLIENT_SECRET');

        if (empty($clientId) || empty($clientSecret)) {
            $this->error('GOOGLE_DRIVE_CLIENT_ID dan GOOGLE_DRIVE_CLIENT_SECRET harus diisi di .env terlebih dahulu.');
            return self::FAILURE;
        }

        $redirectUri = env('APP_URL', 'http://localhost:8000') . '/google-drive/callback';

        $client = new Client();
        $client->setClientId($clientId);
        $client->setClientSecret($clientSecret);
        $client->setRedirectUri($redirectUri);
        $client->addScope('https://www.googleapis.com/auth/drive.file');
        $client->setAccessType('offline');
        $client->setPrompt('consent');

        $authUrl = $client->createAuthUrl();

        $this->newLine();
        $this->info('=== Google Drive OAuth Authorization ===');
        $this->newLine();
        $this->line('Browser akan terbuka secara otomatis.');
        $this->line('Login dan izinkan akses, lalu tunggu...');
        $this->newLine();

        $this->line('Jika browser tidak terbuka, buka URL ini manual:');
        $this->comment($authUrl);
        $this->newLine();

        $cacheKey = 'google_drive_auth_code';

        // Bersihkan cache lama
        Cache::forget($cacheKey);

        // Buka browser
        if (PHP_OS_FAMILY === 'Darwin') {
            exec("open \"{$authUrl}\"");
        } elseif (PHP_OS_FAMILY === 'Windows') {
            exec("start \"\" \"{$authUrl}\"");
        } else {
            exec("xdg-open \"{$authUrl}\"");
        }

        $this->line('Menunggu authorization callback...');

        // Poll cache sampai code diterima
        $maxWait = 120; // detik
        $waited = 0;

        while ($waited < $maxWait) {
            sleep(2);
            $waited += 2;

            $code = Cache::get($cacheKey);

            if ($code) {
                Cache::forget($cacheKey);
                break;
            }

            $this->line("  Menunggu... ({$waited}s)");
        }

        if (empty($code)) {
            $this->error('Timeout. Tidak ada callback yang diterima dalam 2 menit.');
            return self::FAILURE;
        }

        try {
            $client->fetchAccessTokenWithAuthCode($code);
            $token = $client->getAccessToken();

            if (empty($token['refresh_token'])) {
                $this->error('Gagal mendapat refresh token.');
                return self::FAILURE;
            }

            $refreshToken = $token['refresh_token'];

            $this->newLine();
            $this->info('Berhasil! Refresh token:');
            $this->newLine();
            $this->comment("GOOGLE_DRIVE_REFRESH_TOKEN={$refreshToken}");
            $this->newLine();
            $this->line('Salin baris di atas ke file .env Anda.');
            $this->newLine();

            $envPath = base_path('.env');
            if (file_exists($envPath)) {
                $envContent = file_get_contents($envPath);
                if (preg_match('/^GOOGLE_DRIVE_REFRESH_TOKEN=.*/m', $envContent)) {
                    $envContent = preg_replace('/^GOOGLE_DRIVE_REFRESH_TOKEN=.*/m', "GOOGLE_DRIVE_REFRESH_TOKEN={$refreshToken}", $envContent);
                } else {
                    $envContent = rtrim($envContent) . "\nGOOGLE_DRIVE_REFRESH_TOKEN={$refreshToken}\n";
                }
                file_put_contents($envPath, $envContent);
                $this->info('Refresh token otomatis disimpan ke .env');
            }

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Gagal menukar authorization code: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
