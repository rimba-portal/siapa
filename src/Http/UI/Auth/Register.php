<?php

declare(strict_types=1);

namespace Rimba\Who\Http\UI\Auth;

use Filament\Auth\Http\Responses\Contracts\RegistrationResponse;
use Filament\Auth\Pages\Register as BaseRegister;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Rimba\Who\Actions\RegisterUser;

class Register extends BaseRegister
{
    protected string $view = 'bites::auth.register';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required()->maxLength(255),
            TextInput::make('username')->required()->unique(config('auth.providers.users.model'))->maxLength(255),
            $this->getEmailFormComponent(),
            $this->getPasswordFormComponent(),
            $this->getPasswordConfirmationFormComponent(),
        ])->statePath('data');
    }

    public function register(): ?RegistrationResponse
    {
        $data = $this->form->getState();

        $authenticatable = app(RegisterUser::class)->handle(
            name: $data['name'],
            email: $data['email'],
            password: $data['password'],
            authIdentifier: $data['username'],
        );

        Auth::login($authenticatable);

        return app(RegistrationResponse::class);
    }
}
