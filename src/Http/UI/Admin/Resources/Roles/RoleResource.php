<?php

declare(strict_types=1);

namespace Rimba\Who\Http\UI\Admin\Resources\Roles;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Rimba\Who\Http\UI\Admin\Resources\Roles\Pages\CreateRole;
use Rimba\Who\Http\UI\Admin\Resources\Roles\Pages\EditRole;
use Rimba\Who\Http\UI\Admin\Resources\Roles\Pages\ListRoles;
use Rimba\Who\Http\UI\Admin\Resources\Roles\Schemas\RoleForm;
use Rimba\Who\Http\UI\Admin\Resources\Roles\Tables\RolesTable;
use Spatie\Permission\Models\Role;
use UnitEnum;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Authorization';

    public static function form(Schema $schema): Schema
    {
        return RoleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RolesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRoles::route('/'),
            'create' => CreateRole::route('/create'),
            'edit' => EditRole::route('/{record}/edit'),
        ];
    }
}
