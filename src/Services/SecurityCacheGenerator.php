<?php

declare(strict_types=1);

namespace Rimba\Who\Services;

use RuntimeException;

final class SecurityCacheGenerator
{
    private const CACHE_DIRECTORY =
        'bootstrap/cache/rimba';

    private const ROUTES_FILE =
        self::CACHE_DIRECTORY.'/routes.php';

    private const SECURITY_FILE =
        self::CACHE_DIRECTORY.'/security.php';

    public function __construct(
        private RouteDiscoveryService $routeDiscoveryService,
        private SecuritySettingsService $securitySettingsService,
    ) {}

    public function generate(): array
    {
        return [
            'routes' => $this->generateRoutes(),
            'policies' => $this->generateSecurityPolicies(),
        ];
    }

    public function generateRoutes(): array
    {
        $routes = $this->routeDiscoveryService
            ->discover();

        $this->writeCacheFile(
            base_path(self::ROUTES_FILE),
            $routes,
        );

        return $routes;
    }

    public function generateSecurityPolicies(): array
    {
        $policies = $this
            ->securitySettingsService
            ->all();

        $this->writeCacheFile(
            base_path(self::SECURITY_FILE),
            $policies,
        );

        return $policies;
    }

    private function writeCacheFile(
        string $file,
        array $data,
    ): void {
        $this->ensureCacheDirectory();

        $temporaryFile = $file.'.tmp';

        $bytes = file_put_contents(
            $temporaryFile,
            $this->exportPhpArray($data),
            LOCK_EX,
        );

        if ($bytes === false) {
            throw new RuntimeException(
                sprintf(
                    'Unable to write security cache file: %s',
                    $temporaryFile,
                ),
            );
        }

        @chmod(
            $temporaryFile,
            0664,
        );

        if (! rename($temporaryFile, $file)) {
            @unlink($temporaryFile);

            throw new RuntimeException(
                sprintf(
                    'Unable to replace security cache file: %s',
                    $file,
                ),
            );
        }

        @chmod(
            $file,
            0664,
        );
    }

    private function ensureCacheDirectory(): void
    {
        $directory = base_path(
            self::CACHE_DIRECTORY,
        );

        if (is_dir($directory)) {
            return;
        }

        if (
            ! mkdir(
                $directory,
                0775,
                true,
            )
            && ! is_dir($directory)
        ) {
            throw new RuntimeException(
                sprintf(
                    'Unable to create security cache directory: %s',
                    $directory,
                ),
            );
        }
    }

    private function exportPhpArray(
        array $data,
    ): string {
        return '<?php'
            .PHP_EOL
            .PHP_EOL
            .'declare(strict_types=1);'
            .PHP_EOL
            .PHP_EOL
            .'return '
            .var_export($data, true)
            .';'
            .PHP_EOL;
    }
}
