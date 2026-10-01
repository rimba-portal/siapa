<?php

declare(strict_types=1);

namespace Rimba\Who\Services;

use Illuminate\Support\Str;
use Rimba\Who\Enums\SecurityLevel;
use Throwable;

final class SecurityPolicyManager
{
    private const SECURITY_FILE =
        'bootstrap/cache/rimba/security.php';

    public function __construct(
        private SecuritySettingsService $securitySettingsService,
        private SecurityCacheGenerator $securityCacheGenerator,
    ) {}

    public function requiredLevel(
        string $routeName,
    ): SecurityLevel {
        foreach (
            $this->policies() as $level => $patterns
        ) {
            foreach ($patterns as $pattern) {
                if (Str::is($pattern, $routeName)) {
                    return $this->resolveLevel(
                        $level,
                    );
                }
            }
        }

        return SecurityLevel::Authenticated;
    }

    private function policies(): array
    {
        $file = base_path(
            self::SECURITY_FILE,
        );

        if (file_exists($file)) {
            $policies = require $file;

            if (is_array($policies)) {
                return $policies;
            }
        }

        /*
         * Failsafe:
         *
         * If bootstrap cache was cleared, retrieve the persisted
         * policies from the database and try to rebuild the cache.
         */
        $policies = $this
            ->securitySettingsService
            ->all();

        try {
            $this
                ->securityCacheGenerator
                ->generateSecurityPolicies();
        } catch (Throwable) {
            /*
             * Continue using database policies for this request if the
             * application process cannot write into bootstrap/cache.
             */
        }

        return $policies;
    }

    private function resolveLevel(
        string $level,
    ): SecurityLevel {
        return match ($level) {
            SecuritySettingsService::FACE_VERIFIED => SecurityLevel::FaceVerified,

            SecuritySettingsService::TWO_FACTOR_VERIFIED => SecurityLevel::TwoFactorVerified,

            default => SecurityLevel::Authenticated,
        };
    }
}
