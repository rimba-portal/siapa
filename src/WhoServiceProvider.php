<?php

declare(strict_types=1);

namespace Rimba\Who;

use Rimba\Base\BitesServiceProvider;


class WhoServiceProvider extends BitesServiceProvider
{
    protected string $configFile = __DIR__ . '/../config/bites.php';
    protected string $viewsPath = __DIR__ . '/../resources/views';
    protected string $iconsPath = __DIR__ . '/../resources/svg';

    protected function bootPackage(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->publishes([__DIR__.'/../resources/assets/models' => public_path('models'),], 'assets');
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

    }
    protected function registerPackage(): void
    {
        //
    }

}
