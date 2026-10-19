<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class AionCharacterController extends Controller
{
    public function index(Request $request): View
    {
        $username = (string) $request->session()->get('aion_account_username', '');
        $accountUid = (int) $request->session()->get('aion_account_uid', 0);
        $characters = collect();
        $charactersAvailable = true;

        // Read-only character query against the supplied AionWorld_1 schema.
        // account_id is matched to the authenticated user_account.uid, and
        // account_name is additionally constrained to the signed-in username.
        try {
            $worldDatabase = (string) config('aion_game.world_database', 'AionWorld_1');
            if (! preg_match('/^[A-Za-z0-9_]+$/', $worldDatabase)) {
                throw new \RuntimeException('Invalid configured AION world database name.');
            }

            $characters = DB::connection((string) config('aion_registration.connection', 'aion_game'))
                ->table($worldDatabase . '.dbo.user_data')
                ->select([
                    'char_id', 'user_id', 'account_id', 'account_name', 'race', 'class',
                    'is_banned', 'gender', 'world', 'lev', 'exp', 'abyss_point',
                    'create_date', 'last_login_time', 'last_logout_time', 'playtime',
                    'delete_date', 'guild_id', 'guild_rank',
                ])
                ->where('account_id', $accountUid)
                ->where('account_name', $username)
                ->where('delete_date', 0)
                ->orderByDesc('lev')
                ->orderBy('user_id')
                ->limit(100)
                ->get();
        } catch (Throwable $exception) {
            $charactersAvailable = false;
            Log::warning('AION character list query failed.', [
                'exception' => $exception::class,
                'account_uid' => $accountUid,
            ]);
        }

        return view('pages.characters', [
            'accountUsername' => $username,
            'characters' => $characters,
            'charactersAvailable' => $charactersAvailable,
            'worldMapNames' => config('aion_world_maps', []),
        ]);
    }
}
