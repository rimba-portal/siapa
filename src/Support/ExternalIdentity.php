<?php

declare(strict_types=1);

namespace Rimba\Who\Support;

final readonly class ExternalIdentity
{
    public function __construct(public string $provider, public string $canonicalIdentifier, public object $subject) {}
}
