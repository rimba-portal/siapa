<?php

declare(strict_types=1);

namespace Rimba\Who\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Rimba\Who\Services\RouteDiscoveryService;

#[Description('Generate Rimba Who security cache files')]
#[Signature('who:security-cache')]
final class GenerateSecurityCacheCommand extends Command
{
    public function handle(
        RouteDiscoveryService $routeDiscoveryService,
    ): int {

        $routes =
            $routeDiscoveryService->discover();

        /*
         * Generate who-routes.php
         */

        file_put_contents(
            base_path(
                'bootstrap/cache/who-routes.php'
            ),
            '<?php return '.
            var_export($routes, true).
            ';'
        );

        /*
         * Generate who-security.php
         * if missing
         */

        $securityFile = base_path(
            'bootstrap/cache/who-security.php'
        );

        if (! file_exists($securityFile)) {

            file_put_contents(
                $securityFile,
                '<?php return '.
                var_export([
                    'authenticated' => [],
                    'two_factor_verified' => [],
                    'face_verified' => [],
                ], true)
                .';'
            );
        }

        $this->info(
            sprintf(
                'Generated %d routes.',
                count($routes)
            )
        );

        $this->components->info(
            'bootstrap/cache/who-routes.php'
        );

        $this->components->info(
            'bootstrap/cache/who-security.php'
        );

        return self::SUCCESS;
    }
}
