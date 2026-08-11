<?php

declare(strict_types=1);

namespace Rimba\Who\Contracts;

use Rimba\Who\Support\AuthenticationResult;

interface AuthOrchestratorContract
{
    public function authenticate(string $identifier, string $password): AuthenticationResult;
}
