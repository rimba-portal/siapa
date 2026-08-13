<?php

declare(strict_types=1);

namespace Rimba\Who\Actions;

use Rimba\Who\Models\AuthenticationAttempt;
use Rimba\Who\Support\AuthenticationResult;

final class RecordAuthenticationAttempt
{
    public function handle(string $identifier, AuthenticationResult $result): AuthenticationAttempt
    {
        return AuthenticationAttempt::query()->create(['user_id' => $result->user?->getAuthIdentifier(), 'provider' => $result->provider, 'identifier' => $identifier, 'event' => 'login', 'success' => $result->succeeded(), 'message' => $result->reason, 'ip_address' => request()->ip(), 'user_agent' => request()->userAgent()]);
    }
}
