<?php

declare(strict_types=1);

namespace Rimba\Who\Enums;

enum SecurityLevel: int
{
    case Public = 0;
    case Authenticated = 1;
    case TwoFactorVerified = 2;
    case FaceVerified = 3;
}
