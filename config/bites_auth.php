<?php

declare(strict_types=1);

return [
    'authentication' => ['providers' => ['ldap', 'local']],
    'security' => ['face_auth_timeout_minutes' => 10, 'face_match_threshold' => 0.50],
    'roles' => ['team_planner' => 'team_planner', 'admin' => 'admin'],
];
