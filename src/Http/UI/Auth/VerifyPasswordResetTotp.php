<?php

declare(strict_types=1);

namespace Rimba\Who\Http\UI\Auth;

use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\SimplePage;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FA\Google2FA;
use Rimba\Who\Models\AuthenticationAttempt;
use Rimba\Who\Models\UserAuth;

class VerifyPasswordResetTotp extends SimplePage
{
    protected string $view =
        'bites::auth.verify-password-reset-totp';

    protected static ?string $title =
        'Verify Authenticator';

    public ?array $data = [];

    public string $token = '';

    public function mount(
        string $token,
    ): void {
        $this->token = $token;

        $this->resolveRecoveryUserAuth();
    }

    public function form(
        Schema $schema,
    ): Schema {
        return $schema
            ->components([
                TextInput::make('code')
                    ->label('Authenticator code')
                    ->helperText(
                        'Enter the current six-digit code from your authenticator app.'
                    )
                    ->required()
                    ->numeric()
                    ->length(6)
                    ->autocomplete('one-time-code')
                    ->autofocus(),
            ])
            ->statePath('data');
    }

    public function verify(): void
    {
        $userAuth = $this->resolveRecoveryUserAuth();

        $code = (string) (
            $this->form->getState()['code'] ?? ''
        );

        $valid = app(Google2FA::class)->verifyKey(
            (string) $userAuth->totp_secret,
            $code,
        );

        AuthenticationAttempt::query()->create([
            'user_id' => $userAuth->user_id,
            'provider' => 'totp',
            'identifier' => $userAuth->auth_identifier,
            'event' => 'password_reset_totp',
            'success' => $valid,
            'message' => $valid
                ? 'recovery_totp_verified'
                : 'invalid_recovery_totp',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        if (! $valid) {
            throw ValidationException::withMessages([
                'data.code' => 'The authenticator code is invalid.',
            ]);
        }

        $resetToken = Str::random(64);

        session()->put([
            'siapa.password_reset.totp_verified' => true,

            'siapa.password_reset.reset_token_hash' => hash('sha256', $resetToken),
        ]);

        Notification::make()
            ->title('Authenticator verified')
            ->success()
            ->send();

        $this->redirect(
            route(
                'siapa.password.reset',
                ['token' => $resetToken],
            )
        );
    }

    private function resolveRecoveryUserAuth(): UserAuth
    {
        $userAuthId = session(
            'siapa.password_reset.user_auth_id'
        );

        $expectedHash = session(
            'siapa.password_reset.verification_token_hash'
        );

        $expiresAt = session(
            'siapa.password_reset.expires_at'
        );

        abort_unless(
            filled($userAuthId)
            && filled($expectedHash)
            && filled($expiresAt)
            && now()->timestamp <= (int) $expiresAt
            && hash_equals(
                (string) $expectedHash,
                hash('sha256', $this->token),
            ),
            403,
            'The password recovery session is invalid or expired.',
        );

        $userAuth = UserAuth::query()
            ->findOrFail($userAuthId);

        abort_unless(
            $userAuth->auth_provider === 'local',
            403,
            'Only local passwords may be reset here.',
        );

        return $userAuth;
    }
}
