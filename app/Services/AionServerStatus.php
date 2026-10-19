<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

class AionServerStatus
{
    /**
     * Read-only website metrics.
     *
     * Server reachability is determined by a short TCP probe to the configured
     * game port. This confirms that something accepts TCP connections; it does
     * not prove that the game world is fully healthy. Player counts still come
     * from recent user_count statistics and are never fabricated.
     */
    public function snapshot(): array
    {
        $cacheSeconds = max(5, (int) config('aion_game.cache_seconds', 30));

        return Cache::remember('aion_game.status_snapshot.v3', $cacheSeconds, function (): array {
            $result = [
                'connection' => 'unknown',
                'server_status' => 'UNKNOWN',
                'server_status_detail' => 'Server reachability has not been verified.',
                'online_players' => null,
                'players_detail' => 'Live player statistics are not currently available.',
                'last_record_at' => null,
                'checked_at' => now(),
            ];

            // A TCP probe works while the game server is unavailable for
            // database status reporting, but the detail explicitly limits what it proves.
            [$reachable, $probeDetail] = $this->probeGamePort();
            $result['server_status'] = $reachable ? 'ONLINE' : 'OFFLINE';
            $result['server_status_detail'] = $probeDetail;

            try {
                $connection = (string) config('aion_game.connection', 'aion_game');
                $database = (string) config('aion_game.database', 'AionAccounts');

                // A successful read confirms SQL connectivity, not game availability.
                DB::connection($connection)
                    ->table($database . '.dbo.worldstatus')
                    ->select(['idx', 'server', 'status', 'no'])
                    ->limit(20)
                    ->get();

                $result['connection'] = 'connected';

                $configuredServerIds = config('aion_game.player_server_ids', [1]);
                if (is_string($configuredServerIds)) {
                    $configuredServerIds = explode(',', $configuredServerIds);
                }

                $serverIds = array_values(array_unique(array_filter(
                    array_map('intval', (array) $configuredServerIds),
                    static fn (int $id): bool => $id >= 0 && $id <= 255,
                )));

                if ($serverIds === []) {
                    $result['players_detail'] = 'No game server IDs are configured for player statistics.';
                    return $result;
                }

                // Only count configured game-world server IDs. In this AION
                // setup, MainServer/config.xml declares serverId=1; server_id=0
                // may represent another statistics source and must not be blindly added.
                $rows = DB::connection($connection)
                    ->table($database . '.dbo.user_count')
                    ->select(['record_time', 'server_id', 'world_user'])
                    ->whereIn('server_id', $serverIds)
                    ->orderByDesc('record_time')
                    ->get();

                if ($rows->isEmpty()) {
                    $result['players_detail'] = 'No player statistics were found for the configured game server IDs.';
                    return $result;
                }

                // Keep the newest record per configured server ID.
                $latestByServer = [];
                foreach ($rows as $row) {
                    $serverId = (string) $row->server_id;
                    if (! array_key_exists($serverId, $latestByServer)) {
                        $latestByServer[$serverId] = $row;
                    }
                }

                $timestamps = array_map(
                    static fn ($row): int => strtotime((string) $row->record_time) ?: 0,
                    array_values($latestByServer),
                );
                $latestTimestamp = $timestamps === [] ? 0 : max($timestamps);
                $result['last_record_at'] = $latestTimestamp > 0
                    ? now()->setTimestamp($latestTimestamp)
                    : null;

                $freshnessSeconds = max(30, (int) config('aion_game.freshness_seconds', 300));
                $allRowsFresh = true;

                // Require a fresh row for every configured game server. This
                // avoids presenting partial totals as the full live population.
                foreach ($serverIds as $serverId) {
                    $row = $latestByServer[(string) $serverId] ?? null;
                    if ($row === null) {
                        $allRowsFresh = false;
                        break;
                    }

                    $rowTimestamp = strtotime((string) $row->record_time) ?: 0;
                    $rowAge = $rowTimestamp > 0 ? now()->timestamp - $rowTimestamp : PHP_INT_MAX;
                    if ($rowAge < 0 || $rowAge > $freshnessSeconds) {
                        $allRowsFresh = false;
                        break;
                    }
                }

                if (! $allRowsFresh) {
                    $result['players_detail'] = 'Player statistics are stale or missing for one or more configured game servers.';
                    return $result;
                }

                $result['online_players'] = array_sum(array_map(
                    static fn ($row): int => max(0, (int) $row->world_user),
                    array_values($latestByServer),
                ));
                $result['players_detail'] = 'Based on recent statistics from configured game server IDs.';

                return $result;
            } catch (Throwable $exception) {
                // Do not leak connection strings or credentials into the page.
                report($exception);
                $result['connection'] = 'unavailable';
                $result['players_detail'] = 'Game database is unavailable or not configured; live player statistics cannot be verified.';
                return $result;
            }
        });
    }

    /** @return array{0: bool, 1: string} */
    private function probeGamePort(): array
    {
        $host = trim((string) config('aion_game.status_host', '127.0.0.1'));
        $port = (int) config('aion_game.status_port', 7777);
        $timeout = max(0.2, min(3.0, (float) config('aion_game.status_timeout', 1.0)));

        if ($host === '' || $port < 1 || $port > 65535) {
            return [false, 'Game port health check is not configured correctly.'];
        }

        $errno = 0;
        $error = '';
        $socket = @stream_socket_client(
            sprintf('tcp://%s:%d', $host, $port),
            $errno,
            $error,
            $timeout,
            STREAM_CLIENT_CONNECT,
        );

        if (is_resource($socket)) {
            fclose($socket);
            return [true, sprintf('TCP port %d on %s accepts connections; full game-world health is not verified.', $port, $host)];
        }

        return [false, sprintf('TCP port %d on %s is not reachable; the game service may be stopped or using a different endpoint.', $port, $host)];
    }
}
