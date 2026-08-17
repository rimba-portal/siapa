<?php

declare(strict_types=1);

namespace Rimba\Who\Http\UI\Admin\Resources\Permissions\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Spatie\Permission\Models\Role;

class PermissionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('guard_name')
                    ->required(),
                TextInput::make('description'),
                Select::make('roles')
                    ->multiple()
                    ->preload()
                    ->relationship(name: 'roles', titleAttribute: 'name')
                    ->getOptionLabelFromRecordUsing(fn (Role $record): string => sprintf('%s - %s', $record->name, $record->team_id)),

            ]);
    }
}
