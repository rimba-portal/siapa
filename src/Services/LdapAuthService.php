<?php

declare(strict_types=1);

namespace Rimba\Who\Services;

use Illuminate\Contracts\Auth\Authenticatable;
use Rimba\Who\Contracts\AuthProviderContract;
use Rimba\Who\Enums\AuthenticationStatus;
use Rimba\Who\Support\AuthenticationResult;
use Throwable;

/**
 * Adapter for the LDAP package.
 *
 * Bind `siapa.ldap.connection` to the concrete LDAP client supplied by
 * rimba/ldap. The client is expected to expose findByIdentifier() and
 * attempt() methods. Adapt these two calls if the LDAP package API differs.
 */
final readonly class LdapAuthService implements AuthProviderContract
{
    public function __construct(private object $connection) {}

    public function name(): string
    {
        return 'ldap';
    }

    public function authenticate(string $identifier, string $password): AuthenticationResult
    {
        try {
            $ldapUser = $this->connection->findByIdentifier($identifier);

            if (! $ldapUser) {
                return new AuthenticationResult(AuthenticationStatus::NotFound, $this->name());
            }

            if (! $this->connection->attempt($identifier, $password)) {
                return new AuthenticationResult(AuthenticationStatus::Failed, $this->name(), reason: 'invalid_credentials');
            }

            $user = $this->resolveLocalUser($ldapUser, $identifier);

            return new AuthenticationResult(AuthenticationStatus::Success, $this->name(), $user);
        } catch (Throwable $throwable) {
            report($throwable);

            return new AuthenticationResult(AuthenticationStatus::Failed, $this->name(), reason: 'provider_error');
        }
    }

    private function resolveLocalUser(object|array $ldapUser, string $identifier): Authenticatable
    {
        $model = config('auth.providers.users.model');
        $attributes = is_array($ldapUser) ? $ldapUser : get_object_vars($ldapUser);
        $email = $attributes['email'] ?? $attributes['mail'] ?? null;

        return $model::query()->firstOrCreate(
            ['username' => $identifier],
            ['email' => $email, 'name' => $attributes['name'] ?? $identifier],
        );
    }
}
