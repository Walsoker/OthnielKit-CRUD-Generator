<?php

namespace Othnielkit\Crud;

use Illuminate\Support\ServiceProvider;

class OthnielkitCrudServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\Commands\HelloCommand::class,
            ]);
        }
    }

    public function register()
    {
        //
    }
}