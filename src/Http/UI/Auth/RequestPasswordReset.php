<?php

declare(strict_types=1);

namespace Rimba\Who\Http\UI\Auth;

use Filament\Auth\Pages\PasswordReset\RequestPasswordReset as BaseRequestPasswordReset;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Rimba\Who\Models\UserAuth;

class RequestPasswordReset extends BaseRequestPasswordReset
{
    public function request(): void
    {
        $data = $this->form->getState();

        $identifier = mb_strtolower(
            trim(
                (string) (
                    $data['email']
                    ?? $data['identifier']
                    ?? ''
                )
            )
        );

        $userAuth = $this->resolveUserAuth($identifier);

        if (! $userAuth) {
            throw ValidationException::withMessages([
                'data.email' => 'No matching account was found.',
            ]);
        }

        if ($userAuth->auth_provider === 'ldap') {
            Notification::make()
                ->title('LDAP password')
                ->body(
                    'This password is managed through LDAP or Active Directory.'
                )
                ->warning()
                ->send();

            return;
        }

        if ($userAuth->auth_provider !== 'local') {
            throw ValidationException::withMessages([
                'data.email' => 'Password recovery is unavailable for this account.',
            ]);
        }

        if (! $userAuth->hasTotp()) {
            throw ValidationException::withMessages([
                'data.email' => 'Recovery TOTP has not been configured for this account.',
            ]);
        }

        $token = Str::random(64);

        session()->put([
            'siapa.password_reset.user_auth_id' => $userAuth->getKey(),

            'siapa.password_reset.verification_token_hash' => hash('sha256', $token),

            'siapa.password_reset.expires_at' => now()->addMinutes(10)->timestamp,

            'siapa.password_reset.totp_verified' => false,
        ]);

        $this->redirect(
            route(
                'siapa.password.verify-totp',
                ['token' => $token],
            )
        );
    }

    private function resolveUserAuth(
        string $identifier,
    ): ?UserAuth {
        $builder = UserAuth::query()->with('user');

        if (
            filter_var(
                $identifier,
                FILTER_VALIDATE_EMAIL,
            )
        ) {
            return $builder
                ->whereHas(
                    'user',
                    fn ($query) => $query->whereRaw(
                        'LOWER(email) = ?',
                        [$identifier],
                    )
                )
                ->first();
        }

        return $builder
            ->where(
                'auth_identifier',
                $identifier,
            )
            ->first();
    }
}
