<?php

declare(strict_types=1);

namespace Rimba\Who\Services;

use Illuminate\Support\Facades\DB;
use Rimba\Who\Models\SecuritySetting;

final class SecuritySettingsService
{
    public const AUTHENTICATED =
        'authenticated';

    public const TWO_FACTOR_VERIFIED =
        'two_factor_verified';

    public const FACE_VERIFIED =
        'face_verified';

    public function defaults(): array
    {
        return [
            self::AUTHENTICATED => [],
            self::TWO_FACTOR_VERIFIED => [],
            self::FACE_VERIFIED => [],
        ];
    }

    public function all(): array
    {
        $settings = SecuritySetting::query()
            ->whereIn(
                'key',
                array_keys($this->defaults()),
            )
            ->pluck('value', 'key')
            ->all();

        return $this->normalize(
            array_merge(
                $this->defaults(),
                $settings,
            ),
        );
    }

    public function save(array $policies): array
    {
        $policies = $this->normalize(
            array_merge(
                $this->defaults(),
                $policies,
            ),
        );

        DB::transaction(
            function () use ($policies): void {
                foreach ($policies as $key => $routes) {
                    SecuritySetting::query()
                        ->updateOrCreate(
                            [
                                'key' => $key,
                            ],
                            [
                                'value' => $routes,
                            ],
                        );
                }
            },
        );

        return $policies;
    }

    private function normalize(
        array $policies,
    ): array {
        $normalized = [];

        foreach (
            array_keys($this->defaults()) as $key
        ) {
            $routes = $policies[$key] ?? [];

            if (! is_array($routes)) {
                $routes = [];
            }

            $routes = array_filter(
                $routes,
                static fn (mixed $route): bool => is_string($route)
                    && trim($route) !== '',
            );

            $routes = array_map(
                static fn (string $route): string => trim($route),
                $routes,
            );

            $routes = array_values(
                array_unique($routes),
            );

            sort(
                $routes,
                SORT_NATURAL | SORT_FLAG_CASE,
            );

            $normalized[$key] = $routes;
        }

        return $normalized;
    }
}
