<?php

declare(strict_types=1);

namespace Rimba\Who\Http\UI\Auth;

use Filament\Forms\Components\TextInput;
use Filament\Pages\SimplePage;
use Filament\Schemas\Schema;
use PragmaRX\Google2FA\Google2FA;
use Rimba\Who\Models\UserAuth;

class SetupTwoFactor extends SimplePage
{
    public ?array $data = [];

    public function mount(): void
    {
        $userAuth = UserAuth::query()
            ->firstOrCreate([
                'user_id' => auth()->id(),
            ]);

        if (blank($userAuth->totp_secret)) {
            $userAuth->update([
                'totp_secret' => app(Google2FA::class)
                    ->generateSecretKey(),
            ]);
        }
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->label('Authenticator code')
                    ->required()
                    ->numeric()
                    ->length(6),
            ])
            ->statePath('data');
    }

    public function confirm(): void
    {
        $userAuth = UserAuth::query()
            ->where('user_id', auth()->id())
            ->firstOrFail();

        abort_unless(
            app(Google2FA::class)->verifyKey(
                (string) $userAuth->totp_secret,
                (string) $this->data['code']
            ),
            422
        );

        $userAuth->update([
            'setup_completed' => true,
        ]);

        $this->redirect(
            route('siapa.face.verify')
        );
    }
}
