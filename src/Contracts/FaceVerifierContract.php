<?php

declare(strict_types=1);

namespace Rimba\Who\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;

interface FaceVerifierContract
{
    public function recordVerification(Authenticatable $user, ?string $ipAddress = null, ?string $userAgent = null): void;
}
