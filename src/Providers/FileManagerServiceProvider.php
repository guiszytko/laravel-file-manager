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
        if (!class_exists('CreateFilesTable')) {
            $this->publishes([
                __DIR__ . '/../migrations/create_files_table.stub' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_files_table.php'),
            ], 'migrations');
        }
        $this->publishes([
            __DIR__ . '/../config/file-manager.php' => config_path('file-manager.php'),
        ], 'config');


    }
}
