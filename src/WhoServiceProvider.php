<?php

declare(strict_types=1);

namespace Rimba\Who;

use Rimba\Base\Services\BitesServiceProvider;
use Rimba\Who\Contracts\AuthOrchestratorContract;
use Rimba\Who\Contracts\FaceVerifierContract;
use Rimba\Who\Contracts\PanelAccessResolverContract;
use Rimba\Who\Contracts\SecurityContextContract;
use Rimba\Who\Contracts\StaffResolverContract;
use Rimba\Who\Services\AuthOrchestratorService;
use Rimba\Who\Services\FaceAuthService;
use Rimba\Who\Services\LdapAuthService;
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
        $this->app->singleton(LocalAuthService::class);
        $this->app->bind(SecurityContextContract::class, SecurityContextService::class);
        $this->app->bind(StaffResolverContract::class, StaffResolverService::class);
        $this->app->bind(PanelAccessResolverContract::class, PanelAccessService::class);
        $this->app->bind(FaceVerifierContract::class, FaceAuthService::class);
        if ($this->app->bound('bites_auth.ldap.connection')) {
            $this->app->singleton('bites_auth.auth-provider.ldap', fn ($app): LdapAuthService => new LdapAuthService($app->make('bites_auth.ldap.connection')));
        }

        $this->app->singleton(function ($app): AuthOrchestratorContract {
            $providers = [];
            foreach (config('bites_auth.authentication.providers', ['local']) as $name) {
                if ($name === 'local') {
                    $providers[] = $app->make(LocalAuthService::class);
                } elseif ($app->bound("bites_auth.auth-provider.{$name}")) {
                    $providers[] = $app->make("bites_auth.auth-provider.{$name}");
                }
            }

            return new AuthOrchestratorService($providers);
        });

    }
}
