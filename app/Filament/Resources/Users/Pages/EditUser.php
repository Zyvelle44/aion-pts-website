<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->visible(fn (): bool =>
                    auth()->user()?->hasRole('super_admin') ?? false
                )
                ->disabled(fn (): bool =>
                    (int) $this->record->getKey() === (int) auth()->id()
                )
                ->requiresConfirmation(),
        ];
    }

    protected function beforeSave(): void
    {
        abort_unless(
            auth()->user()?->hasRole('super_admin'),
            403
        );

        // Jangan izinkan Super Admin menghapus role super_admin
        // dari akun yang sedang digunakan.
        if ((int) $this->record->getKey() === (int) auth()->id()) {
            $roles = $this->data['roles'] ?? [];

            $superAdminRole = \Spatie\Permission\Models\Role::query()
                ->where('name', 'super_admin')
                ->where('guard_name', 'web')
                ->first();

            if (
                $superAdminRole
                && ! in_array((string) $superAdminRole->getKey(), array_map('strval', $roles), true)
            ) {
                abort(403, 'You cannot remove your own Super Admin role.');
            }
        }
    }

    protected function beforeDelete(): void
    {
        abort_unless(
            auth()->user()?->hasRole('super_admin'),
            403
        );

        abort_if(
            (int) $this->record->getKey() === (int) auth()->id(),
            403,
            'You cannot delete your own account.'
        );
    }
}