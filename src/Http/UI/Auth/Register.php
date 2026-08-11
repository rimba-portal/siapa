<?php

declare(strict_types=1);

namespace Rimba\Who\Http\UI\Auth;

use Filament\Auth\Pages\Register as BaseRegister;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class Register extends BaseRegister
{
    protected string $view = 'siapa::auth.register';

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
}
