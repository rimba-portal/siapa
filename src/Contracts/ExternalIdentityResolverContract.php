<?php

declare(strict_types=1);

namespace Rimba\Who\Contracts;

use Rimba\Who\Support\ExternalIdentity;

interface ExternalIdentityResolverContract
{
    public function name(): string;

    public function find(string $identifier): ?ExternalIdentity;
}
