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
        if (class_exists(\Illuminate\Foundation\Console\ServeCommand::class)) {
            \Illuminate\Foundation\Console\ServeCommand::$passthroughVariables = array_merge(
                \Illuminate\Foundation\Console\ServeCommand::$passthroughVariables,
                [
                    'Path',
                    'PATH',
                    'PATHEXT',
                    'pathext',
                    'ProgramData',
                    'SystemRoot',
                    'windir',
                    'WINDIR',
                    'SYSTEMDRIVE',
                    'SystemDrive',
                    'TEMP',
                    'TMP',
                    'USERPROFILE',
                    'LOCALAPPDATA',
                    'APPDATA',
                    'CommonProgramFiles',
                    'ProgramFiles',
                    'ProgramFiles(x86)',
                    'ComSpec',
                    'COMSPEC',
                    'OS',
                    'HOMEDRIVE',
                    'HOMEPATH',
                    'PSModulePath',
                ]
            );
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
