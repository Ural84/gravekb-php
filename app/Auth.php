<?php
declare(strict_types=1);

namespace App;

final class Auth
{
    public const COOKIE = 'gravekb_session';

    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    public static function makeUserId(string $email): string
    {
        return substr(hash('sha256', strtolower(trim($email))), 0, 16);
    }

    public static function publicUser(array $user): array
    {
        return [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'phone' => $user['phone'],
            'createdAt' => $user['created_at'],
            'companyName' => $user['company_name'] ?? '',
            'inn' => $user['inn'] ?? '',
            'ogrn' => $user['ogrn'] ?? '',
            'bik' => $user['bik'] ?? '',
            'checkingAccount' => $user['checking_account'] ?? '',
        ];
    }

    public static function createSession(string $userId): string
    {
        $token = bin2hex(random_bytes(32));
        $now = gmdate('c');
        $expires = gmdate('c', time() + 60 * 60 * 24 * 30);
        Database::pdo()->prepare(
            'INSERT INTO sessions (token, user_id, created_at, expires_at) VALUES (?, ?, ?, ?)'
        )->execute([$token, $userId, $now, $expires]);
        setcookie(self::COOKIE, $token, [
            'expires' => time() + 60 * 60 * 24 * 30,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        return $token;
    }

    public static function logout(): void
    {
        $token = $_COOKIE[self::COOKIE] ?? '';
        if ($token) {
            Database::pdo()->prepare('DELETE FROM sessions WHERE token = ?')->execute([$token]);
        }
        setcookie(self::COOKIE, '', [
            'expires' => time() - 3600,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    public static function currentUser(): ?array
    {
        $token = $_COOKIE[self::COOKIE] ?? '';
        if ($token === '') {
            return null;
        }
        $stmt = Database::pdo()->prepare(
            'SELECT u.* FROM sessions s JOIN users u ON u.id = s.user_id
             WHERE s.token = ? AND s.expires_at > ? LIMIT 1'
        );
        $stmt->execute([$token, gmdate('c')]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function getByEmail(string $email): ?array
    {
        $stmt = Database::pdo()->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([strtolower(trim($email))]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function saveUser(array $user): void
    {
        Database::pdo()->prepare(
            'INSERT INTO users (id, name, email, phone, password_hash, created_at, company_name, inn, ogrn, bik, checking_account)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
             ON CONFLICT(id) DO UPDATE SET
               name=excluded.name, phone=excluded.phone, company_name=excluded.company_name,
               inn=excluded.inn, ogrn=excluded.ogrn, bik=excluded.bik, checking_account=excluded.checking_account'
        )->execute([
            $user['id'],
            $user['name'],
            $user['email'],
            $user['phone'],
            $user['password_hash'],
            $user['created_at'],
            $user['company_name'] ?? '',
            $user['inn'] ?? '',
            $user['ogrn'] ?? '',
            $user['bik'] ?? '',
            $user['checking_account'] ?? '',
        ]);
    }
}
