<?php

declare(strict_types=1);

namespace Rimba\Who\Http\Middleware;

use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Rimba\Who\Contracts\PanelAccessResolverContract;
use Symfony\Component\HttpFoundation\Response;

final readonly class EnsurePanelAccess
{
    public function __construct(
        private PanelAccessResolverContract $panelAccessResolverContract,
    ) {}

    public function handle(
        Request $request,
        Closure $next,
        ?string $panelId = null,
    ): Response {
        $user = Filament::auth()->user();

        /*
         * Filament's Authenticate middleware should normally handle this.
         * Keep this fallback so the middleware remains safe if its order changes.
         */
        if (! $user) {
            return redirect()->guest(
                Filament::getCurrentPanel()?->getLoginUrl()
                    ?? route('filament.lobby.auth.login')
            );
        }

        /*
         * Prefer the explicit middleware parameter:
         *
         * EnsurePanelAccess::class . ':admin'
         *
         * Otherwise use the current Filament panel ID.
         */
        $panelId ??= Filament::getCurrentPanel()?->getId();

        if (
            blank($panelId)
            || ! $this->panelAccessResolverContract->canAccess($user, $panelId)
        ) {
            $destination = $this->panelAccessResolverContract
                ->destinationFor($user);

            $destinationPanel = Filament::getPanel($destination);

            /*
             * Redirect to the user's permitted destination instead of
             * returning 403. Prevent redirect loops if the resolver returns
             * the same inaccessible panel.
             */
            if (
                $destinationPanel
                && $destination !== $panelId
            ) {
                return redirect()->to(
                    $destinationPanel->getUrl()
                );
            }

            abort(403);
        }

        return $next($request);
    }
}
