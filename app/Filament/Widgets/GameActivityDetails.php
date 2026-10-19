<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

class GameActivityDetails extends Widget
{
    protected string $view = 'filament.widgets.game-activity-details';
    protected static ?int $sort = -19;
    protected int|string|array $columnSpan = 'full';

    protected function getViewData(): array
    {
        return Cache::remember('aion.dashboard.game_activity_details.v1', 60, function () {
            $data = ['classes'=>[], 'recent_characters'=>[], 'recent_accounts'=>[], 'online'=>null, 'errors'=>[]];
            try {
                $connection = (string) config('aion_registration.connection', config('aion_game.connection', 'aion_game'));
                $accountsDb = (string) config('aion_game.database', 'AionAccounts');
                $worldDb = (string) env('AION_WORLD_DATABASE', 'AionWorld_1');
                $db = DB::connection($connection);
                $classMeta = [
                    0 => ['Warrior', 'warrior.png'],
                    1 => ['Gladiator', 'gladiator.png'],
                    2 => ['Templar', 'templar.png'],
                    3 => ['Scout', 'scout.png'],
                    4 => ['Assassin', 'assassin.png'],
                    5 => ['Ranger', 'ranger.png'],
                    6 => ['Mage', 'mage.png'],
                    7 => ['Sorcerer', 'sorcerer.png'],
                    8 => ['Spirit Master', 'spiritmaster.png'],
                    9 => ['Priest', 'priest.png'],
                    10 => ['Cleric', 'cleric.png'],
                    11 => ['Chanter', 'chanter.png'],
                    12 => ['Rider', 'rider.png'],
                    13 => ['Aethertech', 'rider.png'],
                    14 => ['Gunner', 'gunner.png'],
                    15 => ['Bard', 'bard.png'],
                    16 => ['Songweaver', 'bard.png'],
                ];
                $data['class_meta'] = $classMeta;
                $data['classes'] = $db->table($worldDb.'.dbo.user_data')
                    ->select('class', DB::raw('COUNT(*) as total'))
                    ->where('delete_date', 0)
                    ->groupBy('class')
                    ->orderByDesc('total')
                    ->limit(12)
                    ->get()
                    ->map(function ($r) use ($classMeta) {
                        $classId = (int) $r->class;
                        return [
                            'id' => $classId,
                            'label' => $classMeta[$classId][0] ?? (string) $classId,
                            'icon' => $classMeta[$classId][1] ?? 'warrior.png',
                            'total' => (int) $r->total,
                        ];
                    })->all();
                $data['recent_characters'] = $db->table($worldDb.'.dbo.user_data')
                    ->select(['char_id','user_id','account_name','race','class','lev','create_date'])
                    ->where('delete_date', 0)
                    ->orderByDesc('create_date')
                    ->limit(8)
                    ->get()
                    ->map(function ($r) use ($classMeta) {
                        $item = (array) $r;
                        $classId = (int) ($item['class'] ?? -1);
                        $item['class_label'] = $classMeta[$classId][0] ?? (string) $classId;
                        $item['class_icon'] = $classMeta[$classId][1] ?? 'warrior.png';
                        $item['race_label'] = match ((int) ($item['race'] ?? -1)) {
                            0 => 'Elyos',
                            1 => 'Asmodians',
                            default => 'Unknown Race',
                        };
                        return $item;
                    })->all();
                $webAccountAvailable = app(\App\Services\AionWebAccountSchema::class)->tableExists($connection, $accountsDb);
                if ($webAccountAvailable) {
                    try {
                        $data['recent_accounts'] = $db->table($accountsDb.'.dbo.web_account')
                            ->select(['account','create_date'])->orderByDesc('create_date')->limit(8)
                            ->get()->map(fn($r)=>(array)$r)->all();
                    } catch (Throwable) {
                        $data['errors'][] = 'Recent account dates are unavailable for this Producer schema.';
                    }
                } else {
                    // Fallback to the core account table, without pretending it has a creation date.
                    try {
                        $data['recent_accounts'] = $db->table($accountsDb.'.dbo.user_auth')
                            ->select(['account'])->orderBy('account')->limit(8)
                            ->get()->map(function ($r) {
                                return ['account' => $r->account, 'create_date' => null];
                            })->all();
                        $data['errors'][] = 'web_account is not installed; account creation dates are unavailable.';
                    } catch (Throwable) {
                        $data['errors'][] = 'Account activity is unavailable for this Producer schema.';
                    }
                }
                try { $data['online'] = app(\App\Services\AionServerStatus::class)->snapshot(); } catch (Throwable) {}
            } catch (Throwable $e) {
                report($e);
                $data['errors'][] = 'Game database statistics are unavailable. Check AION database connection and database names in .env.';
            }
            return $data;
        });
    }
}
