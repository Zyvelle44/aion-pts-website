<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use RuntimeException;

class AionLoginService
{
    public function attempt(string $username, string $password): ?object
    {
        if (AionRegistrationService::scrCharFilter($password) !== $password) {
            return null;
        }

        $connectionName = (string) config('aion_registration.connection', config('aion_game.connection', 'aion_game'));
        $database = (string) config('aion_game.database', 'AionAccounts');
        if (! app(AionWebAccountSchema::class)->supports($connectionName, $database, ['uid', 'account', 'email', 'password2'])) {
            throw new RuntimeException('This AION Producer schema does not provide the web_account fields required by website login.');
        }

        $encoded = AionRegistrationService::encryptLegacy($password);
        $connection = DB::connection($connectionName);
        $row = $connection->selectOne(
            'SELECT TOP (1) uid, account, email FROM ' . $this->qualifiedDatabase($database) . '.dbo.web_account WHERE account = ? AND LOWER(password2) = LOWER(?)',
            [$username, $encoded],
        );
        return $row ?: null;
    }

    private function qualifiedDatabase(string $database): string
    {
        return '[' . str_replace(']', ']]', trim($database, '[]')) . ']';
    }
}
