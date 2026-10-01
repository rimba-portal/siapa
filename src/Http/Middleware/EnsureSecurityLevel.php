<?php

declare(strict_types=1);

namespace Rimba\Who\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Rimba\Who\Enums\SecurityLevel;
use Rimba\Who\Services\SecurityPolicyManager;
use Symfony\Component\HttpFoundation\Response;

final class EnsureSecurityLevel
{
    public function __construct(
        private SecurityPolicyManager $securityPolicyManager,
    ) {}

    public function handle(
        Request $request,
        Closure $next,
    ): Response {

        $user = auth()->user();

        if (! $user) {
            return $next($request);
        }

        $routeName = $request
            ->route()
            ?->getName();

        if (! $routeName) {
            return $next($request);
        }

        $requiredLevel =
            $this->securityPolicyManager
                ->requiredLevel($routeName);

        $currentLevel =
            $user->userAuth->securityLevel();

        if (
            $currentLevel->value
            >=
            $requiredLevel->value
        ) {
            return $next($request);
        }

        /*
         * Face verification
         */

        if (
            $requiredLevel ===
            SecurityLevel::FaceVerified
        ) {

            session()->put(
                'face_auth.intended_url',
                $request->fullUrl()
            );

            return redirect()->route(
                'filament.staff.pages.verify-face'
            );
        }

        abort(403);
    }
}
