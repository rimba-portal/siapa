<?php

declare(strict_types=1);

namespace Rimba\Who;

use Filament\Facades\Filament;
use Illuminate\Console\Command;
use ReflectionClass;
use Rimba\Base\Services\BitesServiceProvider;
use Rimba\Who\Actions\AuthenticateLocalUser;
use Rimba\Who\Contracts\FaceVerifierContract;
use Rimba\Who\Contracts\IdentityAuthenticatorContract;
use Rimba\Who\Contracts\PanelAccessResolverContract;
use Rimba\Who\Contracts\SecurityContextContract;
use Rimba\Who\Contracts\StaffResolverContract;
use Rimba\Who\Enums\AuthenticationStatus;
use Rimba\Who\Http\Middleware\EnsureSecurityLevel;
use Rimba\Who\Services\FaceAuthService;
use Rimba\Who\Services\IdentityAuthenticatorService;
use Rimba\Who\Services\IdentityResolverService;
use Rimba\Who\Services\PanelAccessService;
use Rimba\Who\Services\SecurityCacheGenerator;
use Rimba\Who\Services\SecurityContextService;
use Rimba\Who\Services\StaffResolverService;
use Rimba\Who\Support\AuthenticationResult;
use Rimba\Who\Support\ExternalIdentity;

class WhoServiceProvider extends BitesServiceProvider
{
    protected string $viewsPath = __DIR__.'/../resources/views';

    protected string $iconsPath = __DIR__.'/../resources/svg';

    protected function bootPackage(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        if ($this->app->runningInConsole()) {
            $this->registerCommandsFromDirectory();
        }

        $this->ensureSecurityCache();
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->publishes([__DIR__.'/../resources/assets/models' => public_path('models')], 'assets');
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        Filament::serving(function (): void {
            app('router')->pushMiddlewareToGroup('web', EnsureSecurityLevel::class);
        });
    }

    protected function registerPackage(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/bites_auth.php', 'bites_auth');
        $this->app->singleton('bites_auth.authenticator.local', fn ($app): IdentityAuthenticatorContract => new class($app->make(AuthenticateLocalUser::class)) implements IdentityAuthenticatorContract
        {
            public function __construct(private AuthenticateLocalUser $authenticateLocalUser) {}

            public function name(): string
            {
                return 'local';
            }

            public function authenticateExisting(string $identifier, string $password): AuthenticationResult
            {
                return $this->authenticateLocalUser->handle($identifier, $password);
            }

            public function authenticateExternal(ExternalIdentity $identity, string $password): AuthenticationResult
            {
                return new AuthenticationResult(AuthenticationStatus::NotFound, 'local', reason: 'unsupported_external_identity');
            }
        });
        $this->app->singleton(IdentityResolverService::class, fn ($app): IdentityResolverService => new IdentityResolverService($this->resolveTagged($app, 'bites_auth.external-resolver')));
        $this->app->singleton(IdentityAuthenticatorService::class, fn ($app): IdentityAuthenticatorService => new IdentityAuthenticatorService($this->resolveTagged($app, 'bites_auth.authenticator')));
        $this->app->tag(['bites_auth.authenticator.local'], 'bites_auth.authenticator');
        $this->app->bind(SecurityContextContract::class, SecurityContextService::class);
        $this->app->bind(StaffResolverContract::class, StaffResolverService::class);
        $this->app->bind(PanelAccessResolverContract::class, PanelAccessService::class);
        $this->app->bind(FaceVerifierContract::class, FaceAuthService::class);
        $this->app->singleton(SecurityCacheGenerator::class);
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

    private function resolveTagged($app, string $tag): iterable
    {
        return $app->tagged($tag);
    }

    protected function ensureSecurityCache(): void
    {
        $cachePath = base_path(
            'bootstrap/cache/rimba'
        );

        if (! is_dir($cachePath)) {

            app(
                SecurityCacheGenerator::class
            )->generate();

            return;
        }

        $requiredFiles = [

            $cachePath.'/who-routes.php',

            $cachePath.'/who-security.php',

        ];

        foreach ($requiredFiles as $requiredFile) {

            if (
                ! file_exists($requiredFile)
                || filesize($requiredFile) === 0
            ) {

                app(
                    SecurityCacheGenerator::class
                )->generate();

                return;
            }
        }
    }
}
