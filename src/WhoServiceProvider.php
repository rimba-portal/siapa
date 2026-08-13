<?php

declare(strict_types=1);

namespace Rimba\Who;

use Illuminate\Console\Command;
use ReflectionClass;
use Rimba\Base\Services\BitesServiceProvider;
use Rimba\Who\Contracts\AuthOrchestratorContract;
use Rimba\Who\Contracts\FaceVerifierContract;
use Rimba\Who\Contracts\PanelAccessResolverContract;
use Rimba\Who\Contracts\SecurityContextContract;
use Rimba\Who\Contracts\StaffResolverContract;
use Rimba\Who\Services\AuthOrchestratorService;
use Rimba\Who\Services\FaceAuthService;
use Rimba\Who\Services\LocalAuthService;
use Rimba\Who\Services\PanelAccessService;
use Rimba\Who\Services\SecurityContextService;
use Rimba\Who\Services\StaffResolverService;

class WhoServiceProvider extends BitesServiceProvider
{
    protected string $viewsPath = __DIR__.'/../resources/views';

    protected string $iconsPath = __DIR__.'/../resources/svg';

    protected function bootPackage(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->publishes([__DIR__.'/../resources/assets/models' => public_path('models')], 'assets');
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

    }

    protected function registerPackage(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/bites_auth.php', 'bites_auth');
        $this->app->singleton('bites_auth.auth-provider.local', LocalAuthService::class);
        $this->app->bind(SecurityContextContract::class, SecurityContextService::class);
        $this->app->bind(StaffResolverContract::class, StaffResolverService::class);
        $this->app->bind(PanelAccessResolverContract::class, PanelAccessService::class);
        $this->app->bind(FaceVerifierContract::class, FaceAuthService::class);
        $this->app->singleton(AuthOrchestratorContract::class, function ($app): AuthOrchestratorContract {
            $providers = [];
            foreach (config('bites_auth.authentication.providers', ['local']) as $name) {
                $binding = "bites_auth.auth-provider.$name";
                if ($app->bound($binding)) {
                    $providers[] = $app->make($binding);
                }
            }

            return new AuthOrchestratorService($providers);
        });

    }

    /**
     * Dynamically discover and boot all commands inside the package directory.
     */
    protected function registerCommandsFromDirectory()
    {
        $commandDir = __DIR__.'/Console/Commands';
        if (! is_dir($commandDir)) {
            return;
        }
        $commands = [];
        foreach (glob($commandDir.'/*.php') as $file) {
            $className = basename($file, '.php');
            $class = 'Rimba\\Who\\Console\\Commands\\'.$className;
            if (class_exists($class) && is_subclass_of($class, Command::class)) {
                $reflection = new ReflectionClass($class);
                if (! $reflection->isAbstract()) {
                    $commands[] = $class;
                }
            }
        }
        if ($commands !== []) {
            $this->commands($commands);
        }
    }
}
