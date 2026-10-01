<?php

declare(strict_types=1);

namespace Rimba\Who\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Rimba\Who\Services\SecurityCacheGenerator;

#[Description('Generate Rimba Who route and security cache files')]
#[Signature('who:security-cache')]
final class GenerateSecurityCacheCommand extends Command
{
    public function handle(
        SecurityCacheGenerator $generator,
    ): int {
        $result = $generator->generate();

        $this->components->info(
            sprintf(
                'Generated route cache containing %d routes.',
                count($result['routes']),
            ),
        );

        $this->components->info(
            sprintf(
                'Generated security cache containing %d policy groups.',
                count($result['policies']),
            ),
        );

        $this->components->info(
            'Cache directory: bootstrap/cache/rimba',
        );

        return self::SUCCESS;
    }
}
