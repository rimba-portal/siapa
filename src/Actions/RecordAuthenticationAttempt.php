<?php

declare(strict_types=1);

namespace Rimba\Who\Actions;

use Illuminate\Contracts\Auth\Authenticatable;
use Rimba\Who\Models\AuthenticationAttempt;

final class RecordAuthenticationAttempt
{
    public function handle(
        string $provider,
        string $identifier,
        string $event,
        bool $success,
        ?Authenticatable $user = null,
        ?string $message = null,
    ): AuthenticationAttempt {
        return AuthenticationAttempt::query()->create([
            'user_id' => $user?->getAuthIdentifier(),
            'provider' => $provider,
            'identifier' => $identifier,
            'event' => $event,
            'success' => $success,
            'message' => $message,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
