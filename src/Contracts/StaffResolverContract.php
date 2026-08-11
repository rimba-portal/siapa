<?php

declare(strict_types=1);

namespace Rimba\Who\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;

interface StaffResolverContract
{
    public function resolve(Authenticatable $user): array;
}
