<?php

declare(strict_types=1);

namespace Rimba\Who\Http\UI\Auth;

use Filament\Forms\Components\TextInput;
use Filament\Pages\SimplePage;
use Filament\Schemas\Schema;
use PragmaRX\Google2FA\Google2FA;
use Rimba\Who\Models\AuthenticationAttempt;
use Rimba\Who\Models\UserAuth;

class VerifyTwoFactor extends SimplePage
{
    protected string $view = 'siapa::auth.verify-two-factor';

    public ?array $data = [];

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('code')->label('Authenticator code')->required()->length(6),
        ])->statePath('data');
    }

    public function verify(): void
    {
        $userAuth = UserAuth::query()->where('user_id', auth()->id())->firstOrFail();
        $valid = app(Google2FA::class)->verifyKey((string) $userAuth->two_factor_secret, (string) $this->data['code']);
        AuthenticationAttempt::query()->create([
            'user_id' => auth()->id(), 'provider' => 'totp', 'identifier' => (string) auth()->id(),
            'event' => '2fa_verified', 'success' => $valid, 'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
        abort_unless($valid, 422);
        session(['siapa.two_factor_verified_at' => now()->toIso8601String()]);
        $this->redirect(route('siapa.face.verify'));
    }
}
