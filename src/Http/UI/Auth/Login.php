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
    // protected string $view = 'bites::auth.login';
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getUsernameFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getRememberFormComponent(),
            ]);
    }

    public function authenticate(): ?LoginResponse
    {
        $data = $this->form->getState();
        $authenticationResult = app(AuthenticateUser::class)->handle(
            identifier: (string) ($data['login'] ?? ''),
            password: (string) ($data['password'] ?? ''),
            remember: (bool) ($data['remember'] ?? false),
        );

        if ($authenticationResult->status === AuthenticationStatus::NotFound) {
            $this->redirect(route('filament.lobby.auth.register', ['identifier' => $data['email'] ?? null]));

            return null;
        }

        if (! $authenticationResult->succeeded()) {
            throw ValidationException::withMessages(['data.email' => __('filament-panels::pages/auth/login.messages.failed')]);
        }

        return app(LoginResponse::class);
    }

    protected function getUsernameFormComponent(): Component
    {
        return TextInput::make('login')
            ->label('Username or email')
            ->required()
            ->autocomplete()
            ->autofocus();
    }

    protected function getCredentialsFromFormData(array $data): array
    {
        $login_type = filter_var($data['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'name';

        return [
            $login_type => $data['login'],
            'password' => $data['password'],
        ];
    }
}
