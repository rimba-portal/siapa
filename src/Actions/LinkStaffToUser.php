<?php

declare(strict_types=1);

namespace Rimba\Who\Actions;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Rimba\Who\Models\UserAuth;

final class LinkStaffToUser
{
    /**
     * Person attributes that must remain attached to User.
     *
     * These represent account preferences rather than the Staff business
     * identity. Add more keys here when required.
     *
     * @var array<int, string>
     */
    private array $userOwnedAttributeKeys = [
        'preferred_language',
        'theme',
        'dashboard_layout',
        'last_seen_panel',
    ];

    public function handle(
        Authenticatable $user,
        ?string $staffNumber,
    ): void {
        if (blank($staffNumber)) {
            return;
        }

        $staffModel = (string) config(
            'bites_auth.staff_model',
        );

        if (
            $staffModel === ''
            || ! class_exists($staffModel)
        ) {
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

        /** @var Model|null $staff */
        $staff = $staffModel::query()
            ->where($staffNumberColumn, trim($staffNumber))
            ->first();

        if (! $staff) {
            return;
        }

        /*
         * Do not detach Staff from a different User automatically.
         */
        $existingUserId = $staff->getAttribute($staffUserColumn);

        if (
            filled($existingUserId)
            && (string) $existingUserId !== (string) $user->getAuthIdentifier()
        ) {
            return;
        }

        DB::transaction(function () use (
            $user,
            $staff,
            $staffUserColumn,
        ): void {
            $staff->forceFill([
                $staffUserColumn => $user->getAuthIdentifier(),
            ])->save();

            $userAuth = UserAuth::query()
                ->where('user_id', $user->getAuthIdentifier())
                ->first();

            /*
             * Attributes remain on User until onboarding is complete.
             */
            if (! $userAuth?->setup_completed) {
                return;
            }

            $this->transferRequiredPersonAttributes(
                user: $user,
                staff: $staff,
            );
        });
    }

    /**
     * Transfer User person attributes to Staff.
     *
     * The existing PersonAttribute rows are reassigned rather than copied,
     * preserving their row IDs and creation timestamps.
     */
    public function transferRequiredPersonAttributes(
        Authenticatable $user,
        Model $staff,
    ): void {
        if (
            ! method_exists($user, 'personAttributes')
            || ! method_exists($staff, 'personAttributes')
        ) {
            return;
        }

        $userAttributes = $user->personAttributes()
            ->whereNotIn('key', $this->userOwnedAttributeKeys)
            ->get();

        foreach ($userAttributes as $userAttribute) {
            /*
             * A Staff attribute may already exist from HR or Weaver sync.
             * Do not silently overwrite an authoritative Staff value.
             */
            $staffAttribute = $staff->personAttributes()
                ->where('key', $userAttribute->key)
                ->first();

            if ($staffAttribute) {
                /*
                 * If the Staff value is empty, accept the onboarding value.
                 */
                if (
                    blank($staffAttribute->value)
                    && filled($userAttribute->value)
                ) {
                    $staffAttribute->forceFill([
                        'value' => $userAttribute->value,
                    ])->save();
                }

                /*
                 * The Staff row now owns this key. Remove the temporary
                 * User-owned onboarding row.
                 */
                $userAttribute->delete();

                continue;
            }

            /*
             * Directly reassociate the polymorphic attribute row.
             */
            $userAttribute->forceFill([
                'attributable_type' => $staff->getMorphClass(),
                'attributable_id' => $staff->getKey(),
            ])->save();
        }
    }
}
