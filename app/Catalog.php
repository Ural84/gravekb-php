<?php
declare(strict_types=1);

namespace App;

use PDO;

final class Catalog
{
    private const HIDDEN_CATEGORIES = [
        'futlyary-i-boksy-dlya-osnastok',
        'flesh-pechati',
    ];

    private const HIDDEN_BRAND_RE =
        '/(?:^|[^a-z0-9])(colop|grm|shiny)(?:[^a-z0-9]|$)|colop-printer|colop-classic|periodichnostyu-grm/i';

    private const NORIS_RE = '/(?:^|[^a-z0-9])noris(?:[^a-z0-9]|$)/i';

    public static function isHiddenProduct(array $p): bool
    {
        if (in_array($p['category'] ?? '', self::HIDDEN_CATEGORIES, true)) {
            return true;
        }
        $blob = implode(' ', [
            $p['name'] ?? '',
            $p['slug'] ?? '',
            $p['subcategory'] ?? '',
            $p['source_path'] ?? '',
        ]);
        if (preg_match(self::HIDDEN_BRAND_RE, $blob)) {
            return true;
        }
        if (
            ($p['category'] ?? '') === 'shtempelnye-podushki' &&
            preg_match(self::NORIS_RE, $blob)
        ) {
            return true;
        }
        return false;
    }

    public static function applyOverride(array $p, ?array $override): array
    {
        if (!$override) {
            return $p;
        }
        if ($override['name'] !== null && $override['name'] !== '') {
            $p['name'] = $override['name'];
        }
        if ($override['description'] !== null) {
            $p['description'] = $override['description'];
        }
        if ($override['price'] !== null) {
            $p['price'] = (float) $override['price'];
        }
        return $p;
    }

    public static function getProducts(?string $q = null, ?string $category = null, ?string $subcategory = null, int $limit = 0): array
    {
        $pdo = Database::pdo();
        $sql = 'SELECT p.*, o.name AS o_name, o.description AS o_description, o.price AS o_price
                FROM products p
                LEFT JOIN product_overrides o ON o.product_id = p.id
                WHERE p.hidden = 0';
        $params = [];
        if ($category) {
            $sql .= ' AND p.category = ?';
            $params[] = $category;
        }
        if ($subcategory) {
            $sql .= ' AND (p.subcategory = ? OR p.subcategory LIKE ?)';
            $params[] = $subcategory;
            $params[] = $subcategory . '/%';
        }
        if ($q) {
            $sql .= ' AND (LOWER(p.name) LIKE ? OR LOWER(COALESCE(o.name, p.name)) LIKE ? OR LOWER(p.description) LIKE ? OR LOWER(p.slug) LIKE ?)';
            $like = '%' . mb_strtolower($q) . '%';
            array_push($params, $like, $like, $like, $like);
        }
        $sql .= ' ORDER BY p.name';
        if ($limit > 0) {
            $sql .= ' LIMIT ' . (int) $limit;
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $out = [];
        foreach ($stmt->fetchAll() as $row) {
            $p = self::rowToProduct($row);
            if (!self::isHiddenProduct($p)) {
                $out[] = $p;
            }
        }
        return $out;
    }

    public static function getBySlug(string $slug): ?array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'SELECT p.*, o.name AS o_name, o.description AS o_description, o.price AS o_price
             FROM products p
             LEFT JOIN product_overrides o ON o.product_id = p.id
             WHERE p.slug = ? LIMIT 1'
        );
        $stmt->execute([$slug]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }
        $p = self::rowToProduct($row);
        return self::isHiddenProduct($p) ? null : $p;
    }

    public static function getById(string $id): ?array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'SELECT p.*, o.name AS o_name, o.description AS o_description, o.price AS o_price
             FROM products p
             LEFT JOIN product_overrides o ON o.product_id = p.id
             WHERE p.id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ? self::rowToProduct($row) : null;
    }

    public static function categories(): array
    {
        $pdo = Database::pdo();
        $rows = $pdo->query('SELECT * FROM categories ORDER BY name')->fetchAll();
        $products = self::getProducts();
        $byCat = [];
        foreach ($products as $p) {
            $byCat[$p['category']][] = $p;
        }
        $out = [];
        foreach ($rows as $row) {
            $slug = $row['slug'];
            if (in_array($slug, self::HIDDEN_CATEGORIES, true)) {
                continue;
            }
            if (empty($byCat[$slug])) {
                continue;
            }
            $data = json_decode($row['data_json'] ?: '{}', true) ?: [];
            $subs = self::filterSubs($data['subcategories'] ?? [], $slug);
            $prices = array_map(fn($p) => $p['price'], $byCat[$slug]);
            $cat = [
                'slug' => $slug,
                'name' => $row['name'],
                'priceFrom' => $prices ? min($prices) : (float) $row['price_from'],
                'subcategories' => [],
            ];
            foreach ($subs as $sub) {
                $subItems = array_filter(
                    $byCat[$slug],
                    fn($p) => ($p['subcategory'] ?? '') === $sub['slug']
                        || str_starts_with((string) ($p['subcategory'] ?? ''), $sub['slug'] . '/')
                );
                $subPrices = array_map(fn($p) => $p['price'], $subItems);
                $cat['subcategories'][] = [
                    'slug' => $sub['slug'],
                    'name' => $sub['name'],
                    'priceFrom' => $subPrices ? min($subPrices) : ($sub['priceFrom'] ?? null),
                ];
            }
            $out[] = $cat;
        }
        return $out;
    }

    public static function getCategory(string $slug): ?array
    {
        foreach (self::categories() as $c) {
            if ($c['slug'] === $slug) {
                return $c;
            }
        }
        return null;
    }

    public static function getSubcategory(string $cat, string $sub): ?array
    {
        $category = self::getCategory($cat);
        if (!$category) {
            return null;
        }
        foreach ($category['subcategories'] as $s) {
            if ($s['slug'] === $sub) {
                return $s;
            }
        }
        return null;
    }

    public static function updateProduct(string $id, array $patch): ?array
    {
        $pdo = Database::pdo();
        $existing = self::getById($id);
        if (!$existing) {
            return null;
        }
        $stmt = $pdo->prepare('SELECT * FROM product_overrides WHERE product_id = ?');
        $stmt->execute([$id]);
        $cur = $stmt->fetch() ?: ['product_id' => $id, 'name' => null, 'description' => null, 'price' => null];

        if (array_key_exists('name', $patch)) {
            $cur['name'] = trim((string) $patch['name']);
        }
        if (array_key_exists('description', $patch)) {
            $cur['description'] = trim((string) $patch['description']);
        }
        if (array_key_exists('price', $patch)) {
            $price = (float) $patch['price'];
            if (!is_finite($price) || $price < 0) {
                throw new \InvalidArgumentException('invalid_price');
            }
            $cur['price'] = round($price, 2);
        }

        $pdo->prepare(
            'INSERT INTO product_overrides (product_id, name, description, price)
             VALUES (?, ?, ?, ?)
             ON CONFLICT(product_id) DO UPDATE SET
               name = excluded.name,
               description = excluded.description,
               price = excluded.price'
        )->execute([$id, $cur['name'], $cur['description'], $cur['price']]);

        // Also update base row so seed stays consistent
        $next = self::applyOverride($existing, $cur);
        $pdo->prepare('UPDATE products SET name = ?, description = ?, price = ? WHERE id = ?')
            ->execute([$next['name'], $next['description'], $next['price'], $id]);

        return self::getById($id);
    }

    public static function hasOverride(string $id): bool
    {
        $stmt = Database::pdo()->prepare('SELECT 1 FROM product_overrides WHERE product_id = ?');
        $stmt->execute([$id]);
        return (bool) $stmt->fetchColumn();
    }

    private static function rowToProduct(array $row): array
    {
        $p = [
            'id' => $row['id'],
            'slug' => $row['slug'],
            'name' => $row['name'],
            'description' => $row['description'] ?? '',
            'price' => (float) $row['price'],
            'image' => $row['image'],
            'category' => $row['category'],
            'subcategory' => $row['subcategory'],
            'sourcePath' => $row['source_path'] ?? '',
            'inStock' => (bool) ($row['in_stock'] ?? 1),
            'unlimited' => (bool) ($row['unlimited'] ?? 0),
        ];
        $override = [
            'name' => $row['o_name'] ?? null,
            'description' => $row['o_description'] ?? null,
            'price' => $row['o_price'] ?? null,
        ];
        return self::applyOverride($p, $override);
    }

    private static function filterSubs(array $subs, string $categorySlug): array
    {
        $out = [];
        foreach ($subs as $s) {
            $blob = ($s['slug'] ?? '') . ' ' . ($s['name'] ?? '');
            if (preg_match(self::HIDDEN_BRAND_RE, $blob)) {
                continue;
            }
            if ($categorySlug === 'shtempelnye-podushki' && preg_match(self::NORIS_RE, $blob)) {
                continue;
            }
            $out[] = $s;
        }
        return $out;
    }

    public static function seedFromJson(): int
    {
        $pdo = Database::pdo();
        $products = json_decode(file_get_contents(Config::root() . '/data/seed/products.json'), true) ?: [];
        $categories = json_decode(file_get_contents(Config::root() . '/data/seed/categories.json'), true) ?: [];

        $pdo->exec('DELETE FROM products');
        $pdo->exec('DELETE FROM categories');

        $insCat = $pdo->prepare(
            'INSERT INTO categories (slug, name, price_from, data_json) VALUES (?, ?, ?, ?)'
        );
        foreach ($categories as $c) {
            $insCat->execute([
                $c['slug'],
                $c['name'],
                (float) ($c['priceFrom'] ?? 0),
                json_encode($c, JSON_UNESCAPED_UNICODE),
            ]);
        }

        $ins = $pdo->prepare(
            'INSERT INTO products (id, slug, name, description, price, image, category, subcategory, source_path, in_stock, unlimited, hidden)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $count = 0;
        foreach ($products as $p) {
            $hidden = self::isHiddenProduct($p) ? 1 : 0;
            $ins->execute([
                $p['id'],
                $p['slug'],
                $p['name'],
                $p['description'] ?? '',
                (float) ($p['price'] ?? 0),
                $p['image'] ?? null,
                $p['category'],
                $p['subcategory'] ?? null,
                $p['sourcePath'] ?? '',
                !empty($p['inStock']) ? 1 : 1,
                !empty($p['unlimited']) ? 1 : 0,
                $hidden,
            ]);
            $count++;
        }
        return $count;
    }
}
