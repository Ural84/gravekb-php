<?php
declare(strict_types=1);

namespace App;

final class Config
{
    private static ?array $data = null;

    public static function load(): array
    {
        if (self::$data !== null) {
            return self::$data;
        }
        $root = dirname(__DIR__);
        $file = $root . '/config.php';
        if (!is_file($file)) {
            $file = $root . '/config.example.php';
        }
        self::$data = require $file;
        return self::$data;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $data = self::load();
        return $data[$key] ?? $default;
    }

    public static function site(): array
    {
        return [
            'name' => 'ГравЕкб',
            'tagline' => 'Оснастки для печатей и штампов в Екатеринбурге',
            'phone' => '+7 999 55 99 019',
            'phoneHref' => 'tel:+79995599019',
            'email' => '3822380@mail.ru',
            'emailHref' => 'mailto:3822380@mail.ru',
            'city' => 'Екатеринбург',
            'workHours' => 'пн–пт с 09:00 до 18:00',
            'address' => 'г. Екатеринбург, ул. Зоологическая, 5а',
        ];
    }

    public static function company(): array
    {
        $c = self::get('company', []);
        $site = self::site();
        return [
            'legalName' => $c['legal_name'] ?? ('ИП / ' . $site['name']),
            'inn' => $c['inn'] ?? '',
            'kpp' => $c['kpp'] ?? '',
            'ogrnip' => $c['ogrnip'] ?? '',
            'address' => $c['address'] ?? $site['address'],
            'bank' => $c['bank'] ?? '',
            'bik' => $c['bik'] ?? '',
            'checkingAccount' => $c['rs'] ?? '',
            'correspondentAccount' => $c['ks'] ?? '',
            'phone' => $site['phone'],
            'email' => $site['email'],
        ];
    }

    public static function siteUrl(): string
    {
        return rtrim((string) self::get('site_url', 'http://localhost:8080'), '/');
    }

    public static function root(): string
    {
        return dirname(__DIR__);
    }

    public static function dbPath(): string
    {
        return self::root() . '/data/gravekb.sqlite';
    }
}
