<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->createPassportKeys();
    }
    protected function createPassportKeys()
    {
        $privateKeyPath = storage_path('oauth-private.key');
        $publicKeyPath = storage_path('oauth-public.key');

        // Check if the private key file does not exist
        if (!File::exists($privateKeyPath)) {
            $privateKey = str_replace('\n', "\n", env('PASSPORT_PRIVATE_KEY'));
            if ($privateKey) {
                File::put($privateKeyPath, $privateKey);
                chmod($privateKeyPath, 0600); // Set appropriate permissions
            }
        }

        // Check if the public key file does not exist
        if (!File::exists($publicKeyPath)) {
            $publicKey = str_replace('\n', "\n", env('PASSPORT_PUBLIC_KEY'));
            if ($publicKey) {
                File::put($publicKeyPath, $publicKey);
                chmod($publicKeyPath, 0600); // Set appropriate permissions
            }
        }
    }
}