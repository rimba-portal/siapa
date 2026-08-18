<?php

declare(strict_types=1);

namespace Rimba\Who\Auth\Responses;

use Filament\Auth\Http\Responses\Contracts\LoginResponse as LoginResponseContract;
use Filament\Facades\Filament;
use Illuminate\Http\RedirectResponse;
use Rimba\Who\Contracts\PanelAccessResolverContract;

final class LoginResponse implements LoginResponseContract
{
    public function toResponse($request): RedirectResponse
    {
        $user = auth()->user();

        $panel = app(
            PanelAccessResolverContract::class
        )->destinationFor($user);

        return redirect()->to(
            Filament::getPanel($panel)->getUrl()
        );
    }
}
