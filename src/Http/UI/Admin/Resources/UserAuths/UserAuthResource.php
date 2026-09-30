<?php

declare(strict_types=1);

namespace Rimba\Who\Http\UI\Admin\Resources\UserAuths;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Rimba\Who\Http\UI\Admin\Resources\UserAuths\Pages\ListUserAuths;
use Rimba\Who\Models\UserAuth;
use UnitEnum;

class UserAuthResource extends Resource
{
    protected static ?string $model = UserAuth::class;

    protected static string|UnitEnum|null $navigationGroup = 'Who';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-play';

    protected static ?int $navigationSort = 44;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([]);
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
            'index' => ListUserAuths::route('/'),
            // 'create' => \Rimba\Who\Http\UI\Admin\Resources\UserAuths\Pages\CreateUserAuth::route('/create'),
            // 'view' => \Rimba\Who\Http\UI\Admin\Resources\UserAuths\Pages\ViewUserAuth::route('/{record}'),
            // 'edit' => \Rimba\Who\Http\UI\Admin\Resources\UserAuths\Pages\EditUserAuth::route('/{record}/edit'),
            //
        ];
    }
}
