<?php

declare(strict_types=1);

namespace Rimba\Who\Services;

final class SecurityCacheGenerator
{
    public function __construct(
        private RouteDiscoveryService $routeDiscoveryService,
    ) {}

    public function generate(): void
    {
        $routes = $this->routeDiscoveryService->discover();

        $this->ensureCacheDirectory();

        $this->writeCacheFile(
            base_path(
                'bootstrap/cache/rimba/who-routes.php'
            ),
            $this->exportPhpArray($routes),
        );

        $securityFile = base_path(
            'bootstrap/cache/rimba/who-security.php'
        );

        if (! file_exists($securityFile)) {

            $this->writeCacheFile(
                $securityFile,
                $this->exportPhpArray([
                    'authenticated' => [],
                    'two_factor_verified' => [],
                    'face_verified' => [],
                ]),
            );
        }
    }

    private function ensureCacheDirectory(): void
    {
        $path = base_path(
            'bootstrap/cache/rimba'
        );

        if (! is_dir($path)) {

            mkdir(
                $path,
                0755,
                true,
            );
        }
    }

    private function writeCacheFile(
        string $file,
        string $contents,
    ): void {

        file_put_contents(
            $file,
            $contents,
            LOCK_EX,
        );

        @chmod(
            $file,
            0664,
        );
    }

    private function exportPhpArray(
        array $array,
    ): string {

        return '<?php'
            .PHP_EOL
            .PHP_EOL
            .'return '
            .var_export($array, true)
            .';'
            .PHP_EOL;
    }
}
