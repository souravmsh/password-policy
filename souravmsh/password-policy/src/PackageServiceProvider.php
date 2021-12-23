<?php 

namespace Souravmsh\PasswordPolicy;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Blade;

use Illuminate\Contracts\Http\Kernel;
use Souravmsh\PasswordPolicy\Http\Middleware\PasswordExpiryCheck;


class PackageServiceProvider extends ServiceProvider
{
    public function boot(Kernel $kernel)
    {
        $this->loadRoutesFrom(__DIR__.'/routes/web.php');

        $this->loadViewsFrom(__DIR__.'/views', 'password-policy');

        $this->loadMigrationsFrom(__DIR__.'/database/migrations');

        $this->publishes([
            __DIR__.'/views' => resource_path('views/vendor/password-policy'),
        ]);

        $this->publishes([
            __DIR__.'/config/password-policy.php' => config_path('password-policy.php')
        ], 'config');

        $this->publishes([
            __DIR__.'/database/migrations' => database_path('migrations')
        ], 'migrations');

        // required * fields
        Blade::directive(
            'required',
            function ($expression) {
                return '<span class="text-danger">*</span>';
            }
        );

        // push middleware - after
        $kernel->pushMiddleware(PasswordExpiryCheck::class);
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/config/password-policy.php', 'password-policy');
    } 
}