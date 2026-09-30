<?php

declare(strict_types=1);

namespace Rimba\Who\Http\UI\Admin\Resources\AuthenticationAttempts;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Rimba\Who\Http\UI\Admin\Resources\AuthenticationAttempts\Pages\ListAuthenticationAttempts;
use Rimba\Who\Models\AuthenticationAttempt;
use UnitEnum;

class AuthenticationAttemptResource extends Resource
{
    protected static ?string $model = AuthenticationAttempt::class;

    protected static string|UnitEnum|null $navigationGroup = 'Who';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-play';

    protected static ?int $navigationSort = 43;

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
            'index' => ListAuthenticationAttempts::route('/'),
            // 'create' => \Rimba\Who\Http\UI\Admin\Resources\AuthenticationAttempts\Pages\CreateAuthenticationAttempt::route('/create'),
            // 'view' => \Rimba\Who\Http\UI\Admin\Resources\AuthenticationAttempts\Pages\ViewAuthenticationAttempt::route('/{record}'),
            // 'edit' => \Rimba\Who\Http\UI\Admin\Resources\AuthenticationAttempts\Pages\EditAuthenticationAttempt::route('/{record}/edit'),
            //
        ];
    }
}
