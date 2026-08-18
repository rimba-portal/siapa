<?php

declare(strict_types=1);

namespace Rimba\Who\Actions;

use Illuminate\Contracts\Auth\Authenticatable;

final class LinkStaffToUser
{
    public function handle(
        Authenticatable $user,
        ?string $staffNumber,
    ): void {
        if (blank($staffNumber)) {
            return;
        }

        $staffModel = (string) config(
            'bites_auth.staff_model'
        );

        if ($staffModel === '' || ! class_exists($staffModel)) {
            return;
        }

        $staffNumberColumn = (string) config(
            'bites_auth.staff_number_column',
            'staff_no',
        );

        $staffUserColumn = (string) config(
            'bites_auth.staff_user_column',
            'user_id',
        );

        $staffModel::query()
            ->where($staffNumberColumn, $staffNumber)
            ->update([
                $staffUserColumn => $user->getAuthIdentifier(),
            ]);
    }
}
