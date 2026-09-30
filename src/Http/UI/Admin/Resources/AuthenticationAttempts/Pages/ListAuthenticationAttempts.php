<?php

declare(strict_types=1);

namespace Rimba\Who\Http\UI\Admin\Resources\AuthenticationAttempts\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Rimba\Who\Http\UI\Admin\Resources\AuthenticationAttempts\AuthenticationAttemptResource;

class ListAuthenticationAttempts extends ListRecords
{
    protected static string $resource = AuthenticationAttemptResource::class;

    protected static ?string $title = 'Authentication Security Logs';

    protected ?string $subheading = 'Audit access verification logs, login statuses, and structural platform requests.';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
