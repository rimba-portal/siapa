<?php

declare(strict_types=1);

namespace Rimba\Who\Services;

use Rimba\Who\Support\ExternalIdentity;

final readonly class IdentityResolverService
{
    public function __construct(private iterable $resolvers) {}

    public function find(string $identifier): ?ExternalIdentity
    {
        foreach ($this->resolvers as $resolver) {
            $identity = $resolver->find($identifier);
            if ($identity) {
                return $identity;
            }
        }

        return null;
    }
}
