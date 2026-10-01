<?php

declare(strict_types=1);

namespace Rimba\Who\Services;

use Illuminate\Support\Facades\Route;

final class RouteDiscoveryService
{
    public function discover(): array
    {
        return collect(Route::getRoutes())
            ->map(fn ($route) => $route->getName())
            ->filter()
            ->sort()
            ->values()
            ->all();
    }
}
