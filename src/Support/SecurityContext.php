<?php

declare(strict_types=1);

namespace Rimba\Who\Support;

use Rimba\Who\Enums\SecurityLevel;

final readonly class SecurityContext
{
    public function __construct(public bool $isStaff, public bool $isTmo, public bool $isAdmin, public SecurityLevel $level) {}
}
