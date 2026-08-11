<?php

declare(strict_types=1);

namespace Rimba\Who\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;
use Rimba\Who\Support\SecurityContext;

interface SecurityContextContract
{
    public function forUser(Authenticatable $user): SecurityContext;
}
