<?php

declare(strict_types=1);

namespace Rimba\Who\Http\UI\Auth;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Auth\MultiFactor\App\AppAuthentication;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthentication;
use Filament\Auth\Pages\PasswordReset\RequestPasswordReset as BaseResetPassword;
use Filament\Forms\Components\OneTimeCodeInput;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class ResetPassword extends BaseResetPassword
{
    public ?string $code = null;

    public bool $verified = false;

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('email')
                ->label('Email')
                ->required()
                ->email(),

            OneTimeCodeInput::make('code')
                ->label('TOTP Code')
                ->required(),

            TextInput::make('password')
                ->label('New Password')
                ->password()
                ->required()
                ->rule(Password::defaults())
                ->visible(fn (): bool => $this->verified),

            TextInput::make('passwordConfirmation')
                ->label('Confirm Password')
                ->password()
                ->required()
                ->same('password')
                ->visible(fn (): bool => $this->verified),
        ]);
    }

    public function verifyTotp(): void
    {
        $data = $this->form->getState();

        $email = $data['email'] ?? null;
        $code = $data['code'] ?? null;

        if (! $email || ! $code) {
            Notification::make()
                ->title('Email and TOTP code are required.')
                ->danger()
                ->send();

            return;
        }

        $user = User::where('email', $email)->first();

        if (! $user) {
            Notification::make()
                ->title('User not found')
                ->danger()
                ->send();

            return;
        }

        if (! $user instanceof HasAppAuthentication) {
            Notification::make()
                ->title('User does not support app authentication.')
                ->danger()
                ->send();

            return;
        }

        $appAuthentication = AppAuthentication::make();

        if (! $appAuthentication->verifyCode($code, $user->app_authentication_secret)) {
            Notification::make()
                ->title('Invalid TOTP code')
                ->danger()
                ->send();

            return;
        }

        $this->verified = true;

        Notification::make()
            ->title('TOTP verified. You may now reset your password.')
            ->success()
            ->send();
    }

    public function resetPassword(): void
    {
        if (! $this->verified) {
            Notification::make()
                ->title('Please verify your TOTP code first.')
                ->danger()
                ->send();

            return;
        }

        $this->rateLimit(2);

        $data = $this->form->getState();

        $user = User::where('email', $data['email'])->first();

        if (! $user) {
            Notification::make()
                ->title('User not found')
                ->danger()
                ->send();

            return;
        }

        $user->forceFill([
            'password' => Hash::make($data['password']),
            'remember_token' => Str::random(60),
        ])->save();

        event(new PasswordReset($user));

        Notification::make()
            ->title('Password reset successfully')
            ->success()
            ->send();

        $this->redirect('/');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('verifyTotp')
                ->label('Verify TOTP')
                ->action('verifyTotp')
                ->visible(fn (): bool => ! $this->verified),

            Action::make('resetPassword')
                ->label('Reset Password')
                ->action('resetPassword')
                ->visible(fn (): bool => $this->verified),
        ];
    }
}
