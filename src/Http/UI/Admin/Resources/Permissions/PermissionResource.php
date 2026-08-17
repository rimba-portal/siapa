<?php

declare(strict_types=1);

namespace Rimba\Who\Http\UI\Admin\Resources\Permissions;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Rimba\Who\Http\UI\Admin\Resources\Permissions\Pages\CreatePermission;
use Rimba\Who\Http\UI\Admin\Resources\Permissions\Pages\EditPermission;
use Rimba\Who\Http\UI\Admin\Resources\Permissions\Pages\ListPermissions;
use Rimba\Who\Http\UI\Admin\Resources\Permissions\Schemas\PermissionForm;
use Rimba\Who\Http\UI\Admin\Resources\Permissions\Tables\PermissionsTable;
use Spatie\Permission\Models\Permission;
use UnitEnum;

class PermissionResource extends Resource
{
    protected static ?string $model = Permission::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Authorization';

    public static function form(Schema $schema): Schema
    {
        return PermissionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PermissionsTable::configure($table);
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
            'index' => ListPermissions::route('/'),
            'create' => CreatePermission::route('/create'),
            'edit' => EditPermission::route('/{record}/edit'),
        ];
    }
}
