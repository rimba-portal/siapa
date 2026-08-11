<?php

declare(strict_types=1);

namespace Rimba\Who\Contracts;

use Rimba\Who\Support\AuthenticationResult;

interface AuthProviderContract
{
    public function name(): string;

    public function authenticate(string $identifier, string $password): AuthenticationResult;
}
