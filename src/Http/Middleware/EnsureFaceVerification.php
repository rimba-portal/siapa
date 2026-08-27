<?php

declare(strict_types=1);

namespace Rimba\Who\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Rimba\Who\Http\UI\Auth\VerifyFace;
use Rimba\Who\Models\UserAuth;
use Rimba\Who\Traits\RequiresFaceVerification;
use Symfony\Component\HttpFoundation\Response;

class EnsureFaceVerification
{
    public function handle(
        Request $request,
        Closure $next,
    ): Response {

        $user = auth()->user();

        if (! $user) {
            return $next($request);
        }

        /*
         * Prevent redirect loop.
         */
        if ($this->isVerifyFacePage($request)) {
            return $next($request);
        }

        /*
         * Detect current page/resource class.
         */
        $class = $this->resolveCurrentClass($request);

        if (! $class) {
            return $next($request);
        }

        /*
         * Only secure pages/resources that use the trait.
         */
        if (! $this->requiresFaceVerification($class)) {
            return $next($request);
        }

        $userAuth = UserAuth::query()
            ->where('user_id', $user->getAuthIdentifier())
            ->first();

        if ($userAuth?->hasValidFaceAuth()) {
            return $next($request);
        }

        /*
         * Save destination.
         */
        session()->put(
            'face_auth.intended_url',
            $request->fullUrl()
        );

        return redirect()->route(
            'siapa.face.verify'
        );
    }

    protected function requiresFaceVerification(
        string $class,
    ): bool {

        return in_array(
            RequiresFaceVerification::class,
            class_uses_recursive($class),
            true,
        );
    }

    protected function isVerifyFacePage(
        Request $request,
    ): bool {

        $class = $this->resolveCurrentClass($request);

        if (! $class) {
            return false;
        }

        return $class === VerifyFace::class;
    }

    protected function resolveCurrentClass(
        Request $request,
    ): ?string {

        $route = $request->route();

        if (! $route) {
            return null;
        }

        /*
         * Filament Page
         */
        if (
            method_exists($route, 'getController')
            && $route->getController()
        ) {
            return get_class(
                $route->getController()
            );
        }

        return null;
    }
}
