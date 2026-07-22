<?php
declare(strict_types=1);

namespace App;

final class Orders
{
    public static function create(array $data): array
    {
        $pdo = Database::pdo();
        $id = bin2hex(random_bytes(8));
        $number = 'GE-' . date('Ymd-His');
        $invoiceNumber = str_replace('GE-', 'СЧ-', $number);
        $items = $data['items'] ?? [];
        $total = 0.0;
        foreach ($items as $item) {
            $total += ((float) ($item['price'] ?? 0)) * max(1, (int) ($item['qty'] ?? 1));
        }
        $now = gmdate('c');
        $pdo->prepare(
            'INSERT INTO orders (
              id, number, type, user_id, name, phone, email, comment, topic, total, status,
              email_sent, invoice_sent, invoice_number, upd_number, created_at,
              company_name, inn, ogrn, bik, checking_account
            ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
        )->execute([
            $id,
            $number,
            $data['type'] ?? 'order',
            $data['user_id'] ?? null,
            $data['name'] ?? '',
            $data['phone'] ?? '',
            $data['email'] ?? '',
            $data['comment'] ?? '',
            $data['topic'] ?? null,
            $total,
            'new',
            0,
            0,
            $invoiceNumber,
            $invoiceNumber,
            $now,
            $data['company_name'] ?? '',
            $data['inn'] ?? '',
            $data['ogrn'] ?? '',
            $data['bik'] ?? '',
            $data['checking_account'] ?? '',
        ]);

        $ins = $pdo->prepare(
            'INSERT INTO order_items (order_id, item_id, slug, name, qty, price, image) VALUES (?,?,?,?,?,?,?)'
        );
        foreach ($items as $i => $item) {
            $ins->execute([
                $id,
                (string) ($item['id'] ?? ('item-' . ($i + 1))),
                $item['slug'] ?? null,
                (string) ($item['name'] ?? ''),
                max(1, (int) ($item['qty'] ?? 1)),
                (float) ($item['price'] ?? 0),
                $item['image'] ?? null,
            ]);
        }

        return self::getById($id);
    }

    public static function getById(string $id): ?array
    {
        $stmt = Database::pdo()->prepare('SELECT * FROM orders WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }
        return self::hydrate($row);
    }

    public static function listAll(): array
    {
        $rows = Database::pdo()->query('SELECT * FROM orders ORDER BY created_at DESC')->fetchAll();
        return array_map([self::class, 'hydrate'], $rows);
    }

    public static function listByUser(string $userId): array
    {
        $stmt = Database::pdo()->prepare('SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC');
        $stmt->execute([$userId]);
        return array_map([self::class, 'hydrate'], $stmt->fetchAll());
    }

    public static function update(string $id, array $patch): ?array
    {
        $order = self::getById($id);
        if (!$order) {
            return null;
        }
        $map = [
            'status' => 'status',
            'name' => 'name',
            'phone' => 'phone',
            'email' => 'email',
            'comment' => 'comment',
            'companyName' => 'company_name',
            'inn' => 'inn',
            'ogrn' => 'ogrn',
            'bik' => 'bik',
            'checkingAccount' => 'checking_account',
            'emailSent' => 'email_sent',
            'invoiceSent' => 'invoice_sent',
        ];
        $fields = [];
        $values = [];
        foreach ($map as $src => $col) {
            if (array_key_exists($src, $patch)) {
                $fields[] = "$col = ?";
                $val = $patch[$src];
                if (in_array($col, ['email_sent', 'invoice_sent'], true)) {
                    $val = $val ? 1 : 0;
                }
                $values[] = $val;
            }
        }

        $pdo = Database::pdo();
        if (isset($patch['items']) && is_array($patch['items'])) {
            $pdo->prepare('DELETE FROM order_items WHERE order_id = ?')->execute([$id]);
            $ins = $pdo->prepare(
                'INSERT INTO order_items (order_id, item_id, slug, name, qty, price, image) VALUES (?,?,?,?,?,?,?)'
            );
            $total = 0.0;
            foreach ($patch['items'] as $i => $item) {
                $qty = max(1, (int) ($item['qty'] ?? 1));
                $price = max(0, (float) ($item['price'] ?? 0));
                $name = trim((string) ($item['name'] ?? ''));
                if ($name === '') {
                    continue;
                }
                $ins->execute([
                    $id,
                    (string) ($item['id'] ?? ('custom-' . ($i + 1))),
                    $item['slug'] ?? null,
                    $name,
                    $qty,
                    $price,
                    $item['image'] ?? null,
                ]);
                $total += $qty * $price;
            }
            $fields[] = 'total = ?';
            $values[] = $total;
        }

        if ($fields) {
            $values[] = $id;
            $pdo->prepare('UPDATE orders SET ' . implode(', ', $fields) . ' WHERE id = ?')->execute($values);
        }
        return self::getById($id);
    }

    public static function delete(string $id): bool
    {
        $stmt = Database::pdo()->prepare('DELETE FROM orders WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }

    private static function hydrate(array $row): array
    {
        $itemsStmt = Database::pdo()->prepare('SELECT * FROM order_items WHERE order_id = ?');
        $itemsStmt->execute([$row['id']]);
        $items = [];
        foreach ($itemsStmt->fetchAll() as $item) {
            $items[] = [
                'id' => $item['item_id'],
                'slug' => $item['slug'],
                'name' => $item['name'],
                'qty' => (int) $item['qty'],
                'price' => (float) $item['price'],
                'image' => $item['image'],
            ];
        }
        return [
            'id' => $row['id'],
            'number' => $row['number'],
            'type' => $row['type'],
            'userId' => $row['user_id'],
            'name' => $row['name'],
            'phone' => $row['phone'],
            'email' => $row['email'],
            'comment' => $row['comment'],
            'topic' => $row['topic'],
            'items' => $items,
            'total' => (float) $row['total'],
            'status' => $row['status'],
            'emailSent' => (bool) $row['email_sent'],
            'invoiceSent' => (bool) $row['invoice_sent'],
            'invoiceNumber' => $row['invoice_number'],
            'updNumber' => $row['upd_number'],
            'createdAt' => $row['created_at'],
            'companyName' => $row['company_name'],
            'inn' => $row['inn'],
            'ogrn' => $row['ogrn'],
            'bik' => $row['bik'],
            'checkingAccount' => $row['checking_account'],
        ];
    }
}
