<?php

declare(strict_types=1);

namespace Rimba\Who\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Rimba\Who\Services\SecurityCacheGenerator;

#[Description('Generate Rimba Who security cache files')]
#[Signature('who:security-cache')]
final class GenerateSecurityCacheCommand extends Command
{
    public function handle(
        SecurityCacheGenerator $generator,
    ): int {

        $generator->generate();

        $this->components->info(
            'Rimba security cache generated.'
        );

        return self::SUCCESS;
    }
}
