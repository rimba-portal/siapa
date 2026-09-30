<?php

namespace Rimba\Who\Http\UI\Admin\Resources\UserAuths\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUserAuths extends ListRecords
{
    protected static string $resource = \Rimba\Who\Http\UI\Admin\Resources\UserAuths\UserAuthResource::class;

    protected static ?string $title = 'User Verification Credentials';

    protected ?string $subheading = 'Manage specific security settings, policies, and linked platform credentials.';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
