<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Exceptions\AionRegistrationException;

class AionRegistrationService
{
    /**
     * Create an AION game account through the legacy stored procedure.
     * This service deliberately does not create a Laravel users row.
     */
    public function register(string $username, string $password, string $email): void
    {
        $connectionName = (string) config('aion_registration.connection', config('aion_game.connection', 'aion_game'));
        $accountsDatabase = (string) config('aion_game.database', 'AionAccounts');
        if (! app(AionWebAccountSchema::class)->supports($connectionName, $accountsDatabase, ['uid', 'account', 'email', 'password2'])) {
            throw new AionRegistrationException('Website account registration is unavailable for this AION Producer schema. The required web_account fields are not installed.');
        }
        $connection = DB::connection($connectionName);

        $existing = $connection->selectOne(
            'SELECT TOP (1) account FROM dbo.user_auth WHERE account = ?',
            [$username],
        );

        if ($existing !== null) {
            throw new AionRegistrationException('That username is already registered.');
        }

        $filteredPassword = self::scrCharFilter($password);
        if ($filteredPassword !== $password) {
            throw new AionRegistrationException('Password contains characters not supported by the legacy AION account system.');
        }

        // These fixed answers match the supplied legacy Producer registration flow.
        // Do not expose them in the browser or log them.
        $answer1 = self::scrCharFilter('2214244rr');
        $answer2 = self::scrCharFilter('sdf3rsfdvv');

        $encryptedPassword = self::encryptLegacy($filteredPassword);
        $encryptedAnswer1 = self::encryptLegacy($answer1);
        $encryptedAnswer2 = self::encryptLegacy($answer2);

        // Values match the provided Producer example. Procedure parameters are bound.
        $connection->statement(
            'EXEC dbo.ap_AutoReg ?, ?, ?, ?, ?, ?, ?',
            [
                $username,
                (int) config('aion_registration.otp_flag', 3),
                $encryptedPassword,
                $encryptedAnswer1,
                $encryptedAnswer2,
                (int) config('aion_registration.password_flag', 0),
                $email,
            ],
        );

        // The supplied procedure does not return a success result. Verify the row exists.
        $created = $connection->selectOne(
            'SELECT TOP (1) account FROM dbo.user_auth WHERE account = ?',
            [$username],
        );

        if ($created === null) {
            throw new AionRegistrationException('The game database did not confirm account creation.');
        }
    }

    /** Preserve the legacy character filter, but callers reject input changed by it. */
    public static function scrCharFilter(string $text): string
    {
        return str_replace(
            ['\\', "'", ',', ';', '--', '-', '%20', '%27', ' ', '`', '=', '%', '"'],
            '',
            $text,
        );
    }

    /**
     * Port of the supplied legacy encrypte() algorithm.
     * The old routine accesses 16 byte positions even for shorter inputs; zero-padding
     * makes those implicit missing bytes explicit and avoids PHP 8 undefined-index notices.
     */
    public static function encryptLegacy(string $value): string
    {
        $bytes = array_values(unpack('C*', substr(str_pad($value, 16, "\0"), 0, 16)) ?: []);
        $bytes = array_pad($bytes, 16, 0);
        $key = [];
        $dst = [];

        for ($i = 1; $i <= 16; $i++) {
            $key[$i] = $bytes[$i - 1];
            $dst[$i] = $key[$i];
        }

        $one = self::u32($key[1] + $key[2] * 256 + $key[3] * 65536 + $key[4] * 16777216);
        $one = self::u32($one * 213119 + 2529077);
        $two = self::u32($key[5] + $key[6] * 256 + $key[7] * 65536 + $key[8] * 16777216);
        $two = self::u32($two * 213247 + 2529089);
        $three = self::u32($key[9] + $key[10] * 256 + $key[11] * 65536 + $key[12] * 16777216);
        $three = self::u32($three * 213203 + 2529589);
        $four = self::u32($key[13] + $key[14] * 256 + $key[15] * 65536 + $key[16] * 16777216);
        $four = self::u32($four * 213821 + 2529997);

        self::writeWord($key, 1, $one);
        self::writeWord($key, 5, $two);
        self::writeWord($key, 9, $three);
        self::writeWord($key, 13, $four);

        $dst[1] = $dst[1] ^ $key[1];
        for ($i = 2; $i <= 16; $i++) {
            $dst[$i] = $dst[$i] ^ $dst[$i - 1] ^ $key[$i];
        }

        $hex = '0x';
        for ($i = 1; $i <= 16; $i++) {
            $byte = $dst[$i] === 0 ? 102 : $dst[$i];
            $hex .= str_pad(dechex($byte), 2, '0', STR_PAD_LEFT);
        }

        return $hex;
    }

    private static function u32(int|float $value): int
    {
        return (int) fmod((float) $value, 4294967296.0);
    }

    private static function writeWord(array &$key, int $start, int $word): void
    {
        $b4 = intdiv($word, 16777216);
        $remainder = $word - $b4 * 16777216;
        // The 65535 divisor is retained from the supplied legacy source for compatibility.
        $b3 = (int) ($remainder / 65535);
        $remainder -= $b3 * 65536;
        $b2 = (int) ($remainder / 256);
        $b1 = (int) ($remainder - $b2 * 256);

        $key[$start] = $b1;
        $key[$start + 1] = $b2;
        $key[$start + 2] = $b3;
        $key[$start + 3] = $b4;
    }
}
