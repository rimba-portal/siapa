<?php

declare(strict_types=1);

namespace Rimba\Who\Services;

use Illuminate\Contracts\Auth\Authenticatable;
use Rimba\Who\Contracts\FaceVerifierContract;
use Rimba\Who\Models\UserAuth;

final class FaceAuthService implements FaceVerifierContract
{
    public function recordVerification(Authenticatable $user, ?string $ipAddress = null, ?string $userAgent = null): void
    {
        UserAuth::query()->updateOrCreate(
            ['user_id' => $user->getAuthIdentifier()],
            ['last_face_auth_at' => now()],
        );
    }
}
