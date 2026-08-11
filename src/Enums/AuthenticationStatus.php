<?php

declare(strict_types=1);

namespace Rimba\Who\Enums;

enum AuthenticationStatus: string
{
    case Success = 'success';
    case Failed = 'failed';
    case NotFound = 'not_found';
}
