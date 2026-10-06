<?php

declare(strict_types=1);

namespace Rimba\Who\Actions;

use Filament\Actions\Action;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class ViewUserAttributesAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'userAttributes';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('My Roles & Attributes')
            ->icon('heroicon-o-key')
            ->slideOver()
            ->modalIcon('heroicon-o-key')
            ->modalWidth(Width::FiveExtraLarge)
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Close')
            ->schema(
                fn (Schema $schema): Schema => $this->buildSchema($schema)
            );
    }

    protected function buildSchema(Schema $schema): Schema
    {
        $user = Auth::user();

        return $schema->components([

            Section::make('Roles')
                ->schema([
                    KeyValueEntry::make('roles')
                        ->label('Assigned Roles')
                        ->state(
                            $this->getRoles()
                        ),
                ])->secondary(),
            Section::make('Attributes')
                ->schema([
                    KeyValueEntry::make('user_attributes')
                        ->label('User Attributes')
                        ->state(
                            $this->toKeyValue(
                                collect($user?->personAttributes)
                            )
                        ),

                    KeyValueEntry::make('staff_attributes')
                        ->label('Staff Attributes')
                        ->state(
                            $this->toKeyValue(
                                collect(
                                    $user?->staff?->personAttributes
                                )
                            )
                        ),

                    KeyValueEntry::make('job_position_attributes')
                        ->label('Job Position Attributes')
                        ->state(
                            $this->toKeyValue(
                                collect(
                                    $user?->staff?->jobPosition?->personAttributes
                                )
                            )
                        ),
                ]),
        ]);
    }

    protected function toKeyValue(Collection $attributes): array
    {
        return $attributes
            ->filter(
                fn ($attribute): bool => filled(
                    $attribute->key ?? null
                )
            )
            ->mapWithKeys(
                fn ($attribute): array => [
                    $attribute->key => $this->normalizeValue(
                        $attribute->value
                    ),
                ]
            )
            ->all();
    }

    protected function getRoles(): array
    {
        $user = Auth::user();

        if ($user === null) {
            return [];
        }

        return [
            'Staff Roles' => collect(
                $user->staff?->roles ?? []
            )
                ->pluck('name')
                ->sort()
                ->implode(', '),

            'Job Position Roles' => collect(
                $user->staff?->jobPosition?->roles ?? []
            )
                ->pluck('name')
                ->sort()
                ->implode(', '),
        ];
    }

    protected function normalizeValue(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if (is_scalar($value)) {
            return (string) $value;
        }

        if ($value instanceof \Stringable) {
            return (string) $value;
        }

        return json_encode(
            $value,
            JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
        ) ?: '';
    }
}
