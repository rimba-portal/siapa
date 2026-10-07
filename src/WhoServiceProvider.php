<?php

declare(strict_types=1);

namespace Rimba\Who;

use Rimba\Base\Services\BitesServiceProvider;

class WhoServiceProvider extends BitesServiceProvider
{
    protected string $iconsPath = __DIR__.'/../resources/svg';

    protected function bootPackage(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        //
    }

    protected function registerPackage(): void
    {
        //
    }
}
