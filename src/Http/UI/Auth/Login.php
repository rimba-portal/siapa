<?php

declare(strict_types=1);

namespace Rimba\Who\Http\UI\Auth;

use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;
use Illuminate\Validation\ValidationException;
use Rimba\Who\Actions\AuthenticateUser;
use Rimba\Who\Enums\AuthenticationStatus;

class Login extends BaseLogin
{
    public function form(Schema $schema): Schema
    {
        return $schema->components([$this->getUsernameFormComponent(), $this->getPasswordFormComponent(), $this->getRememberFormComponent()]);
    }

    public function authenticate(): ?LoginResponse
    {
        $d = $this->form->getState();
        $authenticationResult = app(AuthenticateUser::class)->handle((string) ($d['login'] ?? ''), (string) ($d['password'] ?? ''), (bool) ($d['remember'] ?? false));
        if ($authenticationResult->status === AuthenticationStatus::NotFound) {
            $this->redirect(route('filament.lobby.auth.register', ['identifier' => $d['login'] ?? null]));

            return null;
        }

        if (! $authenticationResult->succeeded()) {
            session()->forget(['auth.pending_user_id', 'auth.pending_remember', 'siapa.two_factor_verified_at']);
            session()->regenerateToken();
            throw ValidationException::withMessages(['data.login' => 'Invalid username or password.']);
        }

        return app(LoginResponse::class);
    }

    protected function getUsernameFormComponent(): Component
    {
        return TextInput::make('login')->label('Username or email')->required()->autocomplete()->autofocus();
    }
}
