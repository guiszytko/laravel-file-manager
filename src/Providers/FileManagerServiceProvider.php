<?php

namespace Guiszytko\LaravelFileManager\Providers;

use Illuminate\Support\ServiceProvider;

class FileManagerServiceProvider extends ServiceProvider
{
    /**
     * Registra os serviços do pacote.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/file-manager.php', 'file-manager');
    }

    /**
     * Bootstrap dos serviços do pacote.
     */
    public function boot()
    {
        // Publicar configurações
        $this->publishes([
            __DIR__ . '/../config/file-manager.php' => config_path('file-manager.php'),
        ], 'config');

        $this->publishes([
            __DIR__ . '/../database/migrations/' => database_path('migrations'),
        ], 'migrations');

     
    }
}
