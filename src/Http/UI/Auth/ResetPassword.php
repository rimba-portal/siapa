<?php

declare(strict_types=1);

namespace Rimba\Who\Http\UI\Auth;

use Filament\Notifications\Notification;
use Filament\Pages\SimplePage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Rimba\Who\Models\AuthenticationAttempt;
use Rimba\Who\Models\UserAuth;

class ResetPassword extends SimplePage
{
    protected string $view = 'bites::auth.reset-password';

    public string $recoveryToken = '';

    public function mount(
        ?string $token = null,
        ?string $email = null,
    ): void {
        $this->recoveryToken = (string) $token;

        $this->resolveRecoveryUserAuth();

        /*
         * Do not call parent::mount().
         *
         * Filament's normal reset page expects Laravel's password-broker
         * token and email flow. This implementation uses a TOTP-verified
         * recovery session instead.
         */
        $this->form->fill([
            'email' => $email,
        ]);
    }

    // public function resetPassword(): ?PasswordResetResponse
    public function resetPassword(): void
    {
        $userAuth = $this->resolveRecoveryUserAuth();
        $data = $this->form->getState();

        DB::transaction(function () use (
            $userAuth,
            $data,
        ): void {
            $user = $userAuth->user;

            abort_unless(
                $user,
                404,
                'The user account no longer exists.',
            );

            $user->forceFill([
                'password' => Hash::make(
                    (string) $data['password']
                ),
            ])->save();

            AuthenticationAttempt::query()->create([
                'user_id' => $userAuth->user_id,
                'provider' => 'local',
                'identifier' => $userAuth->auth_identifier,
                'event' => 'password_reset',
                'success' => true,
                'message' => 'password_reset_completed',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        });

        session()->forget([
            'siapa.password_reset.user_auth_id',
            'siapa.password_reset.verification_token_hash',
            'siapa.password_reset.reset_token_hash',
            'siapa.password_reset.expires_at',
            'siapa.password_reset.totp_verified',
        ]);

        Notification::make()
            ->title('Password updated')
            ->body('Sign in using your new password.')
            ->success()
            ->send();

        $this->redirect(
            filament()
                ->getPanel('lobby')
                ->getLoginUrl()
        );
    }

    private function resolveRecoveryUserAuth(): UserAuth
    {
        $userAuthId = session(
            'siapa.password_reset.user_auth_id'
        );

        $expectedHash = session(
            'siapa.password_reset.reset_token_hash'
        );

        $expiresAt = session(
            'siapa.password_reset.expires_at'
        );

        $verified = session(
            'siapa.password_reset.totp_verified',
            false,
        );

        abort_unless(
            $verified === true
                && filled($userAuthId)
                && filled($expectedHash)
                && filled($expiresAt)
                && now()->timestamp <= (int) $expiresAt
                && hash_equals(
                    (string) $expectedHash,
                    hash(
                        'sha256',
                        $this->recoveryToken,
                    ),
                ),
            403,
            'The password reset session is invalid or expired.',
        );

        $userAuth = UserAuth::query()
            ->with('user')
            ->findOrFail($userAuthId);

        abort_unless(
            $userAuth->auth_provider === 'local',
            403,
            'LDAP passwords cannot be reset through this application.',
        );

        return $userAuth;
    }
}
