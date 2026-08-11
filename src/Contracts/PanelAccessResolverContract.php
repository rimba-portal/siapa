<?php

declare(strict_types=1);

namespace Rimba\Who\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;

interface PanelAccessResolverContract
{
    public function canAccess(Authenticatable $user, string $panelId): bool;

    public function destinationFor(Authenticatable $user): string;
}
