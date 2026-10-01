<?php

declare(strict_types=1);

namespace Rimba\Who\Services;

use Illuminate\Support\Facades\Route;

final class RouteDiscoveryService
{
    public function discover(): array
    {
        return collect(
            Route::getRoutes()
        )
            ->pluck('action.as')
            ->filter()
            ->filter(
                fn (string $route): bool => str_starts_with(
                    $route,
                    'filament.'
                )
            )
            ->sort()
            ->values()
            ->all();
    }
}
