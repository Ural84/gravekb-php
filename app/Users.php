<?php
declare(strict_types=1);

namespace App;

final class Users
{
    public static function listAll(): array
    {
        $users = Database::pdo()->query('SELECT * FROM users ORDER BY created_at DESC')->fetchAll();
        $orders = Orders::listAll();
        $stats = [];
        foreach ($orders as $o) {
            if (!$o['userId']) {
                continue;
            }
            if (!isset($stats[$o['userId']])) {
                $stats[$o['userId']] = ['orderCount' => 0, 'ordersTotal' => 0.0];
            }
            $stats[$o['userId']]['orderCount']++;
            if ($o['type'] === 'order') {
                $stats[$o['userId']]['ordersTotal'] += $o['total'];
            }
        }
        $out = [];
        foreach ($users as $u) {
            $pub = Auth::publicUser($u);
            $s = $stats[$u['id']] ?? ['orderCount' => 0, 'ordersTotal' => 0.0];
            $out[] = $pub + $s;
        }
        return $out;
    }

    public static function delete(string $id): bool
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare('SELECT id FROM users WHERE id = ?');
        $stmt->execute([$id]);
        if (!$stmt->fetch()) {
            return false;
        }
        $pdo->prepare('DELETE FROM sessions WHERE user_id = ?')->execute([$id]);
        $pdo->prepare('UPDATE orders SET user_id = NULL WHERE user_id = ?')->execute([$id]);
        $pdo->prepare('DELETE FROM users WHERE id = ?')->execute([$id]);
        return true;
    }
}
