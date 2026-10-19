<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use RuntimeException;

class AionPasswordService
{
    public function change(string $username, string $newPassword): void
    {
        if (AionRegistrationService::scrCharFilter($newPassword) !== $newPassword) {
            throw new RuntimeException('The new password contains characters not supported by the legacy AION account format.');
        }

        $connectionName = (string) config('aion_registration.connection', config('aion_game.connection', 'aion_game'));
        $database = (string) config('aion_game.database', 'AionAccounts');
        if (! app(AionWebAccountSchema::class)->supports($connectionName, $database, ['account', 'password2'])) {
            throw new RuntimeException('Password changes are unavailable for this AION Producer schema because the required web_account fields are missing.');
        }

        $connection = DB::connection($connectionName);
        $encryptedPassword = AionRegistrationService::encryptLegacy($newPassword);
        $connection->statement('EXEC dbo.ap_AutoChangePwd ?, ?', [$username, $encryptedPassword]);
        $safeDatabase = '[' . str_replace(']', ']]', trim($database, '[]')) . ']';
        $updated = $connection->selectOne(
            'SELECT TOP (1) account FROM ' . $safeDatabase . '.dbo.web_account WHERE account = ? AND LOWER(password2) = LOWER(?)',
            [$username, $encryptedPassword],
        );
        if ($updated === null) {
            throw new RuntimeException('The game database did not confirm the password change. Please contact support before retrying.');
        }
    }
}
