<?php

declare(strict_types=1);

namespace Rimba\Who\Actions;

use Filament\Actions\Action;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
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
                    TextEntry::make('staff_roles')
                        ->label('Staff Roles')
                        ->badge()
                        ->bulleted()
                        ->state(fn (): array => $this->getStaffRoles())
                        ->placeholder('No staff roles assigned'),

                    TextEntry::make('job_position_roles')
                        ->label('Job Position Roles')
                        ->badge()
                        ->state(fn (): array => $this->getJobPositionRoles())
                        ->placeholder('No job position roles assigned'),
                ])
                ->columns(2)
                ->secondary(),
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

    /**
     * @return array<int, string>
     */
    protected function getStaffRoles(): array
    {
        $user = Auth::user();

        if ($user === null) {
            return [];
        }

        return collect($user->staff?->roles ?? [])
            ->pluck('name')
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    protected function getJobPositionRoles(): array
    {
        $user = Auth::user();

        if ($user === null) {
            return [];
        }

        return collect(
            $user->staff?->jobPosition?->roles ?? []
        )
            ->pluck('name')
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();
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
