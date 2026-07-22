<?php
declare(strict_types=1);

namespace App;

final class Captcha
{
    private const TTL_MS = 600000;

    private static function secret(): string
    {
        $s = (string) Config::get('captcha_secret', '');
        if ($s !== '') {
            return $s;
        }
        return (string) Config::get('admin_password', 'gravekb-captcha');
    }

    private static function sign(string $payload): string
    {
        return hash_hmac('sha256', $payload, self::secret());
    }

    public static function create(): array
    {
        $a = 2 + random_int(0, 7);
        $b = 2 + random_int(0, 7);
        $answer = $a + $b;
        $exp = (int) (microtime(true) * 1000) + self::TTL_MS;
        $payload = $answer . '.' . $exp;
        $token = $payload . '.' . self::sign($payload);
        return [
            'question' => $a . ' + ' . $b,
            'token' => $token,
        ];
    }

    public static function verify(string $token, string $answerRaw): bool
    {
        $answer = trim($answerRaw);
        if ($token === '' || $answer === '' || !preg_match('/^\d+$/', $answer)) {
            return false;
        }
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return false;
        }
        [$expected, $expRaw, $signature] = $parts;
        $payload = $expected . '.' . $expRaw;
        if (!hash_equals(self::sign($payload), $signature)) {
            return false;
        }
        $exp = (int) $expRaw;
        if ($exp <= 0 || (int) (microtime(true) * 1000) > $exp) {
            return false;
        }
        return hash_equals((string) ((int) $answer), $expected);
    }
}
