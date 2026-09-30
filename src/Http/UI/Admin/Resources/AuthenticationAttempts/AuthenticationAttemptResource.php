<?php

namespace Rimba\Who\Http\UI\Admin\Resources\AuthenticationAttempts;

use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AuthenticationAttemptResource extends Resource
{
    protected static ?string $model = \Rimba\Who\Models\AuthenticationAttempt::class;

    protected static string|UnitEnum|null $navigationGroup = 'Who';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-play';

    protected static ?int $navigationSort = 43;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema { return $schema->components([]); }

    public static function infolist(Schema $schema): Schema { return $schema->components([]); }

    public static function table(Table $table): Table { return $table->columns([]); }

    public static function getRelations(): array 
    { 
        return [ 
            // 
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => \Rimba\Who\Http\UI\Admin\Resources\AuthenticationAttempts\Pages\ListAuthenticationAttempts::route('/'),
            // 'create' => \Rimba\Who\Http\UI\Admin\Resources\AuthenticationAttempts\Pages\CreateAuthenticationAttempt::route('/create'),
            // 'view' => \Rimba\Who\Http\UI\Admin\Resources\AuthenticationAttempts\Pages\ViewAuthenticationAttempt::route('/{record}'),
            // 'edit' => \Rimba\Who\Http\UI\Admin\Resources\AuthenticationAttempts\Pages\EditAuthenticationAttempt::route('/{record}/edit'),
            //
        ];
    }
}
