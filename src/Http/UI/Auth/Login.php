<?php

declare(strict_types=1);

namespace Rimba\Who\Http\UI\Auth;

use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\Pages\Login as BaseLogin;
use Illuminate\Validation\ValidationException;
use Rimba\Who\Actions\AuthenticateUser;
use Rimba\Who\Enums\AuthenticationStatus;

class Login extends BaseLogin
{
    protected string $view = 'siapa::auth.login';

    public function authenticate(): ?LoginResponse
    {
        $data = $this->form->getState();
        $authenticationResult = app(AuthenticateUser::class)->handle(
            identifier: (string) ($data['email'] ?? ''),
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

        $this->redirect(route('siapa.2fa.verify'));

        return null;
    }
}
