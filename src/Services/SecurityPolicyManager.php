<?php

declare(strict_types=1);

namespace Rimba\Who\Services;

use Illuminate\Support\Str;
use Rimba\Who\Enums\SecurityLevel;

final class SecurityPolicyManager
{
    private function policies(): array
    {
        $file = base_path('bootstrap/cache/rimba/who-security.php');

        if (! file_exists($file)) {
            return [];
        }

        return require $file;
    }

    public function requiredLevel(string $routeName): SecurityLevel
    {
        $policies = $this->policies();

        foreach ($policies as $level => $patterns) {

            foreach ($patterns as $pattern) {

                if (Str::is($pattern, $routeName)) {
                    return match ($level) {

                        'face_verified' => SecurityLevel::FaceVerified,

                        'two_factor_verified' => SecurityLevel::TwoFactorVerified,

                        default => SecurityLevel::Authenticated,
                    };
                }
            }
        }

        return SecurityLevel::Authenticated;
    }
}
