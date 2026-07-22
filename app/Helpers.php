<?php
declare(strict_types=1);

namespace App;

final class Helpers
{
    public static function e(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public static function formatPrice(float|int $value): string
    {
        return number_format((float) $value, 0, '.', ' ') . ' ₽';
    }

    public static function formatPriceFrom(float|int $value): string
    {
        return 'от ' . self::formatPrice($value);
    }

    public static function json(mixed $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public static function readJsonBody(): array
    {
        $raw = file_get_contents('php://input') ?: '';
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }

    public static function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public static function redirect(string $path): never
    {
        header('Location: ' . $path);
        exit;
    }
}
