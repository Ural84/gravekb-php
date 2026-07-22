<?php
declare(strict_types=1);

namespace App;

final class Billing
{
    public static function normalize(array $input): array
    {
        return [
            'companyName' => trim((string) ($input['companyName'] ?? '')),
            'inn' => preg_replace('/\s+/', '', (string) ($input['inn'] ?? '')),
            'ogrn' => preg_replace('/\s+/', '', (string) ($input['ogrn'] ?? '')),
            'bik' => preg_replace('/\s+/', '', (string) ($input['bik'] ?? '')),
            'checkingAccount' => preg_replace('/\s+/', '', (string) ($input['checkingAccount'] ?? '')),
        ];
    }

    public static function validate(array $b): ?string
    {
        if ($b['companyName'] === '') {
            return 'Укажите название организации / ИП';
        }
        if (!preg_match('/^\d{10}(\d{2})?$/', $b['inn'])) {
            return 'ИНН должен содержать 10 или 12 цифр';
        }
        if (!preg_match('/^\d{13}(\d{2})?$/', $b['ogrn'])) {
            return 'ОГРН/ОГРНИП должен содержать 13 или 15 цифр';
        }
        if (!preg_match('/^\d{9}$/', $b['bik'])) {
            return 'БИК должен содержать 9 цифр';
        }
        if (!preg_match('/^\d{20}$/', $b['checkingAccount'])) {
            return 'Расчётный счёт должен содержать 20 цифр';
        }
        return null;
    }
}
