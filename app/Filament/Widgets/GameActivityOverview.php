<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

class GameActivityOverview extends StatsOverviewWidget
{
    protected static ?int $sort = -20;
    protected ?string $heading = 'Game Activity';
    protected ?string $description = 'Read-only statistics from the configured AION game databases.';
    protected function getStats(): array
    {
        $data = Cache::remember('aion.dashboard.game_activity.v1', 60, fn () => $this->collectMetrics());
        return [
            Stat::make('Game Accounts', $this->fmt($data['accounts']))->description('Accounts in user_auth')->icon('heroicon-o-users'),
            Stat::make('Total Characters', $this->fmt($data['characters']))->description('Active characters (not deleted)')->icon('heroicon-o-user-group'),
            Stat::make('New Accounts · 7 days', $this->fmt($data['new_accounts']))->description($data['new_accounts'] === null ? 'Unavailable in this Producer schema' : 'Based on web_account.create_date')->icon('heroicon-o-user-plus'),
            Stat::make('New Characters · 7 days', $this->fmt($data['new_characters']))->description('Characters created in the last 7 days')->icon('heroicon-o-sparkles'),
            Stat::make('Elyos Characters', $this->fmt($data['elyos']))->description('Active characters')->icon('heroicon-o-sun'),
            Stat::make('Asmodian Characters', $this->fmt($data['asmodian']))->description('Active characters')->icon('heroicon-o-moon'),
            Stat::make('Banned Characters', $this->fmt($data['banned']))->description('Active characters flagged as banned')->icon('heroicon-o-no-symbol'),
            Stat::make('Guilds', $this->fmt($data['guilds']))->description('Guild IDs currently in use')->icon('heroicon-o-shield-check'),
        ];
    }

    private function collectMetrics(): array
    {
        $result = ['accounts'=>null,'characters'=>null,'new_accounts'=>null,'new_characters'=>null,'elyos'=>null,'asmodian'=>null,'banned'=>null,'guilds'=>null];
        try {
            $connection = (string) config('aion_registration.connection', config('aion_game.connection', 'aion_game'));
            $accountsDb = (string) config('aion_game.database', 'AionAccounts');
            $worldDb = (string) env('AION_WORLD_DATABASE', 'AionWorld_1');
            $db = DB::connection($connection);
            $result['accounts'] = (int) $db->table($accountsDb.'.dbo.user_auth')->count();

            // web_account is an optional website/Producer extension, not a universal
            // AION PTS table. Keep the dashboard usable when it is absent.
            $webAccountAvailable = app(\App\Services\AionWebAccountSchema::class)->tableExists($connection, $accountsDb);
            if ($webAccountAvailable) {
                try {
                    $result['new_accounts'] = (int) $db->table($accountsDb.'.dbo.web_account')
                        ->where('create_date', '>=', now()->subDays(7))
                        ->count();
                } catch (Throwable) {
                    // Column may differ between Producer versions.
                    $result['new_accounts'] = null;
                }
            }
            $characters = $db->table($worldDb.'.dbo.user_data')->where('delete_date', '=', 0);
            $result['characters'] = (int) (clone $characters)->count();
            $result['new_characters'] = (int) (clone $characters)->where('create_date', '>=', now()->subDays(7))->count();
            // Common AION race values: 0 Elyos, 1 Asmodian. If a server uses different mappings, labels should be adjusted.
            $result['elyos'] = (int) (clone $characters)->where('race', 0)->count();
            $result['asmodian'] = (int) (clone $characters)->where('race', 1)->count();
            $result['banned'] = (int) (clone $characters)->where('is_banned', 1)->count();
            $result['guilds'] = (int) $db->table($worldDb.'.dbo.user_data')->where('delete_date', 0)->where('guild_id', '>', 0)->distinct()->count('guild_id');
        } catch (Throwable $e) {
            report($e);
        }
        return $result;
    }
    private function fmt(?int $value): string { return $value === null ? '—' : number_format($value); }
}
