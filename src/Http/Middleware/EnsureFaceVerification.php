<?php

declare(strict_types=1);

namespace Rimba\Who\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Rimba\Who\Http\UI\Auth\VerifyFace;
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

        $class = $this->resolveCurrentClass($request);

        if (! $class) {
            return $next($request);
        }

        /*
         * Prevent redirect loop.
         */
        if ($class === VerifyFace::class) {
            return $next($request);
        }

        /*
         * Only secure pages/resources that explicitly opt in.
         */
        if (! $this->requiresFaceVerification($class)) {
            return $next($request);
        }

        $userAuth = $user->userAuth;

        if ($userAuth?->hasValidFaceAuth()) {
            return $next($request);
        }

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

    protected function resolveCurrentClass(
        Request $request,
    ): ?string {

        $route = $request->route();

        if (! $route) {
            return null;
        }

        $controller = $route->getController();

        if (! $controller) {
            return null;
        }

        return get_class($controller);
    }
}
