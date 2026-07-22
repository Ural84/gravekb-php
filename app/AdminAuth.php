<?php
declare(strict_types=1);

namespace App;

final class AdminAuth
{
    public const COOKIE = 'gravekb_admin';

    public static function login(string $login, string $password): bool
    {
        $okLogin = hash_equals((string) Config::get('admin_login', 'admin'), $login);
        $okPass = hash_equals((string) Config::get('admin_password', ''), $password);
        if (!$okLogin || !$okPass) {
            return false;
        }
        $token = bin2hex(random_bytes(32));
        $now = gmdate('c');
        $expires = gmdate('c', time() + 60 * 60 * 24 * 7);
        Database::pdo()->prepare(
            'INSERT INTO admin_sessions (token, created_at, expires_at) VALUES (?, ?, ?)'
        )->execute([$token, $now, $expires]);
        setcookie(self::COOKIE, $token, [
            'expires' => time() + 60 * 60 * 24 * 7,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        return true;
    }

    public static function logout(): void
    {
        $token = $_COOKIE[self::COOKIE] ?? '';
        if ($token) {
            Database::pdo()->prepare('DELETE FROM admin_sessions WHERE token = ?')->execute([$token]);
        }
        setcookie(self::COOKIE, '', [
            'expires' => time() - 3600,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    public static function check(): bool
    {
        $token = $_COOKIE[self::COOKIE] ?? '';
        if ($token === '') {
            return false;
        }
        $stmt = Database::pdo()->prepare(
            'SELECT 1 FROM admin_sessions WHERE token = ? AND expires_at > ? LIMIT 1'
        );
        $stmt->execute([$token, gmdate('c')]);
        return (bool) $stmt->fetchColumn();
    }

    public static function require(): void
    {
        if (!self::check()) {
            Helpers::json(['ok' => false, 'error' => 'unauthorized'], 401);
        }
    }
}
