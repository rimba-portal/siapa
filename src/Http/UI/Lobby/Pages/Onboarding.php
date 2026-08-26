<?php

declare(strict_types=1);

namespace Rimba\Who\Http\UI\Lobby\Pages;

use BackedEnum;
use Filament\Auth\MultiFactor\Contracts\MultiFactorAuthenticationProvider;
use Filament\Facades\Filament;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Schema;
use Rimba\Who\Components\WebCam;
use Rimba\Who\Models\UserAuth;
use UnitEnum;

class Onboarding extends Page
{
    protected static string|UnitEnum|null $navigationGroup = 'Profile';

    protected static string|BackedEnum|null $navigationIcon = 'bites-asset-own';

    protected static ?string $navigationLabel = 'Onboarding';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Onboarding';

    protected ?string $subheading = 'Complete your profile and security setup.';

    protected string $view = 'bites::lobby.onboarding';

    public ?array $data = [];

    public function mount(): void
    {
        $user = auth()->user();

        $userAuth = UserAuth::firstOrCreate(
            [
                'user_id' => $user->id,
            ],
            [
                'auth_provider' => 'local',
                'auth_identifier' => $user->email,
            ],
        );

        $attributes = $userAuth->photo ?? [];

        $faceDescriptor = $userAuth->face_descriptor ?? [];

        $this->form->fill([
            'fullname' => data_get($attributes, 'fullname', $user->name),
            'phone' => data_get($attributes, 'phone'),
            'address' => data_get($attributes, 'address'),
            'emergency_contact' => data_get($attributes, 'emergency_contact'),
            'preferred_language' => data_get($attributes, 'preferred_language'),
            'personal_email' => data_get($attributes, 'personal_email'),

            // always a string
            'photo' => data_get($faceDescriptor, 'photo_path'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Wizard::make([
                    Wizard\Step::make('Profile')
                        ->schema([
                            TextInput::make('fullname')
                                ->required(),

                            TextInput::make('phone')
                                ->tel()
                                ->required(),

                            Textarea::make('address')
                                ->rows(3),

                            TextInput::make('emergency_contact'),

                            TextInput::make('preferred_language'),

                            TextInput::make('personal_email')
                                ->email(),
                        ]),

                    Wizard\Step::make('Photo')
                        ->schema([
                            WebCam::make('photo')
                                ->label('Profile Photo')
                                ->required(),
                        ]),

                    Wizard\Step::make('Recovery TOTP')
                        ->schema([
                            Section::make('Recovery Authenticator')
                                ->compact()
                                ->divided()
                                ->schema(
                                    collect(Filament::getMultiFactorAuthenticationProviders())
                                        ->map(
                                            fn (MultiFactorAuthenticationProvider $provider): Group => Group::make(
                                                $provider->getManagementSchemaComponents()
                                            )->statePath($provider->getId())
                                        )
                                        ->all()
                                ),
                        ]),
                ]),
            ])
            ->statePath('data');
    }

    public function finish(): void
    {
        $state = $this->form->getState();

        $user = auth()->user();

        $userAuth = UserAuth::firstOrCreate(
            [
                'user_id' => $user->id,
            ],
            [
                'auth_provider' => 'local',
                'auth_identifier' => $user->email,
            ],
        );

        $userAuth->update([
            'photo' => [
                'fullname' => $state['fullname'] ?? null,
                'phone' => $state['phone'] ?? null,
                'address' => $state['address'] ?? null,
                'emergency_contact' => $state['emergency_contact'] ?? null,
                'preferred_language' => $state['preferred_language'] ?? null,
                'personal_email' => $state['personal_email'] ?? null,
            ],

            'face_descriptor' => [
                'photo_path' => $state['photo'] ?? null,
                'captured_at' => now()->toDateTimeString(),
            ],

            'setup_completed' => true, // Set to true once finished
        ]);

        // Correct redirect syntax for Filament/Livewire pages
        redirect()->intended(filament()->getUrl());
    }
}
