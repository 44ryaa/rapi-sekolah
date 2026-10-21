<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('app.env') === 'production' || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // Vercel Serverless SQLite & Storage Handler (Writable in /tmp)
        if (isset($_ENV['VERCEL']) || isset($_SERVER['VERCEL'])) {
            $tmpDb = '/tmp/database.sqlite';
            $srcDb = database_path('database.sqlite');
            if (!file_exists($tmpDb)) {
                if (file_exists($srcDb)) {
                    copy($srcDb, $tmpDb);
                } else {
                    touch($tmpDb);
                }
            }
            config(['database.connections.sqlite.database' => $tmpDb]);

            $tmpStorage = '/tmp/storage';
            if (!is_dir($tmpStorage)) {
                @mkdir($tmpStorage, 0777, true);
            }
            config(['filesystems.disks.public.root' => $tmpStorage]);
        }
    }
}
