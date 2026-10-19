<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Throwable;

/** Checks optional Producer-specific web_account schema without mutating game DB. */
class AionWebAccountSchema
{
    public function tableExists(?string $connectionName = null, ?string $database = null): bool
    {
        $connectionName ??= (string) config('aion_registration.connection', config('aion_game.connection', 'aion_game'));
        $database ??= (string) config('aion_game.database', 'AionAccounts');
        $safeDatabase = $this->quoteDatabase($database);

        try {
            $row = DB::connection($connectionName)->selectOne(
                "SELECT CASE WHEN OBJECT_ID(?, 'U') IS NULL THEN 0 ELSE 1 END AS table_exists",
                [$safeDatabase . '.dbo.web_account'],
            );
            return (int) ($row->table_exists ?? 0) === 1;
        } catch (Throwable) {
            return false;
        }
    }

    /** Return true only when the table and all required columns exist. */
    public function supports(?string $connectionName = null, ?string $database = null, array $columns = []): bool
    {
        $connectionName ??= (string) config('aion_registration.connection', config('aion_game.connection', 'aion_game'));
        $database ??= (string) config('aion_game.database', 'AionAccounts');
        if (! $this->tableExists($connectionName, $database)) {
            return false;
        }
        if ($columns === []) {
            return true;
        }

        try {
            $safeDatabase = $this->quoteDatabase($database);
            $rows = DB::connection($connectionName)->select(
                'SELECT c.name FROM ' . $safeDatabase . '.sys.columns c '
                . 'INNER JOIN ' . $safeDatabase . '.sys.tables t ON t.object_id = c.object_id '
                . 'INNER JOIN ' . $safeDatabase . '.sys.schemas s ON s.schema_id = t.schema_id '
                . 'WHERE s.name = ? AND t.name = ?',
                ['dbo', 'web_account'],
            );
            $available = array_map(static fn ($row) => strtolower((string) $row->name), $rows);
            foreach ($columns as $column) {
                if (! in_array(strtolower((string) $column), $available, true)) {
                    return false;
                }
            }
            return true;
        } catch (Throwable) {
            return false;
        }
    }

    private function quoteDatabase(string $database): string
    {
        return '[' . str_replace(']', ']]', trim($database, '[]')) . ']';
    }
}
