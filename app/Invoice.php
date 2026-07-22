<?php
declare(strict_types=1);

namespace App;

final class Invoice
{
    public static function numberFromOrder(array $order): string
    {
        return str_replace('GE-', 'СЧ-', $order['number']);
    }

    public static function buildHtml(array $order, ?string $publicUrl = null): string
    {
        $company = Config::company();
        $invoiceNo = $order['invoiceNumber'] ?: self::numberFromOrder($order);
        $created = date('d.m.Y', strtotime($order['createdAt']));
        $rows = '';
        foreach ($order['items'] as $index => $item) {
            $sum = $item['price'] * $item['qty'];
            $rows .= '<tr>'
                . '<td style="padding:8px;border:1px solid #c5d0dc;">' . ($index + 1) . '</td>'
                . '<td style="padding:8px;border:1px solid #c5d0dc;">' . Helpers::e($item['name']) . '</td>'
                . '<td style="padding:8px;border:1px solid #c5d0dc;text-align:center;">шт</td>'
                . '<td style="padding:8px;border:1px solid #c5d0dc;text-align:right;">' . (int) $item['qty'] . '</td>'
                . '<td style="padding:8px;border:1px solid #c5d0dc;text-align:right;">' . Helpers::e(Helpers::formatPrice($item['price'])) . '</td>'
                . '<td style="padding:8px;border:1px solid #c5d0dc;text-align:right;">' . Helpers::e(Helpers::formatPrice($sum)) . '</td>'
                . '</tr>';
        }
        $kpp = $company['kpp'] ? '<br/>КПП ' . Helpers::e($company['kpp']) : '';
        $ogrnip = $company['ogrnip'] ? '<br/>ОГРНИП ' . Helpers::e($company['ogrnip']) : '';
        $buyerInn = $order['inn'] ? 'ИНН ' . Helpers::e($order['inn']) . '<br/>' : '';
        $buyerOgrn = $order['ogrn'] ? 'ОГРН/ОГРНИП ' . Helpers::e($order['ogrn']) . '<br/>' : '';
        $buyerBik = $order['bik'] ? 'БИК ' . Helpers::e($order['bik']) . '<br/>' : '';
        $buyerRs = $order['checkingAccount'] ? 'Р/с ' . Helpers::e($order['checkingAccount']) . '<br/>' : '';
        $comment = $order['comment'] ? '<br/>' . Helpers::e($order['comment']) : '';
        $email = $order['email'] ? '<br/>' . Helpers::e($order['email']) : '';
        $link = $publicUrl ? '<p style="margin:0 0 12px;"><a href="' . Helpers::e($publicUrl) . '">Открыть счёт онлайн</a></p>' : '';

        return '<!DOCTYPE html><html lang="ru"><head><meta charset="utf-8" /><title>Счёт '
            . Helpers::e($invoiceNo) . '</title></head><body style="margin:0;padding:24px;background:#eef2f6;color:#132033;font-family:Arial,sans-serif;">'
            . '<div style="max-width:800px;margin:0 auto;background:#fff;border:1px solid #c5d0dc;border-radius:16px;padding:28px;">'
            . '<h1 style="margin:0 0 8px;font-size:24px;">Счёт на оплату № ' . Helpers::e($invoiceNo) . '</h1>'
            . '<p style="margin:0 0 20px;color:#5a6a7c;">от ' . Helpers::e($created) . ' · заказ ' . Helpers::e($order['number']) . '</p>'
            . '<table style="width:100%;border-collapse:collapse;margin-bottom:20px;"><tr>'
            . '<td style="vertical-align:top;width:50%;padding-right:12px;"><strong>Поставщик</strong><br/>'
            . Helpers::e($company['legalName']) . '<br/>ИНН ' . Helpers::e($company['inn']) . $kpp . $ogrnip . '<br/>'
            . Helpers::e($company['address']) . '<br/>' . Helpers::e($company['phone']) . ' · ' . Helpers::e($company['email'])
            . '</td><td style="vertical-align:top;width:50%;"><strong>Покупатель</strong><br/>'
            . Helpers::e($order['companyName'] ?: $order['name']) . '<br/>' . $buyerInn . $buyerOgrn . $buyerBik . $buyerRs
            . Helpers::e($order['phone']) . $email . $comment
            . '</td></tr></table>'
            . '<table style="width:100%;border-collapse:collapse;margin-bottom:12px;">'
            . '<tr><td style="padding:8px;border:1px solid #c5d0dc;"><strong>Банк</strong><br/>' . Helpers::e($company['bank']) . '</td>'
            . '<td style="padding:8px;border:1px solid #c5d0dc;"><strong>БИК</strong><br/>' . Helpers::e($company['bik']) . '</td></tr>'
            . '<tr><td style="padding:8px;border:1px solid #c5d0dc;"><strong>Р/с</strong><br/>' . Helpers::e($company['checkingAccount']) . '</td>'
            . '<td style="padding:8px;border:1px solid #c5d0dc;"><strong>К/с</strong><br/>' . Helpers::e($company['correspondentAccount']) . '</td></tr></table>'
            . '<table style="width:100%;border-collapse:collapse;margin:20px 0;"><thead><tr style="background:#f3f7fa;">'
            . '<th style="padding:8px;border:1px solid #c5d0dc;">№</th><th style="padding:8px;border:1px solid #c5d0dc;">Товар</th>'
            . '<th style="padding:8px;border:1px solid #c5d0dc;">Ед.</th><th style="padding:8px;border:1px solid #c5d0dc;">Кол-во</th>'
            . '<th style="padding:8px;border:1px solid #c5d0dc;">Цена</th><th style="padding:8px;border:1px solid #c5d0dc;">Сумма</th>'
            . '</tr></thead><tbody>' . $rows . '</tbody></table>'
            . '<p style="text-align:right;font-size:18px;font-weight:700;margin:0 0 8px;">Итого к оплате: '
            . Helpers::e(Helpers::formatPrice($order['total'])) . '</p>'
            . '<p style="color:#5a6a7c;font-size:13px;margin:0 0 18px;">В том числе НДС не облагается / уточняется по вашей системе налогообложения. '
            . 'В назначении платежа укажите: «Оплата по счёту ' . Helpers::e($invoiceNo) . '».</p>'
            . $link . '</div></body></html>';
    }

    public static function buildText(array $order, ?string $publicUrl = null): string
    {
        $company = Config::company();
        $invoiceNo = $order['invoiceNumber'] ?: self::numberFromOrder($order);
        $lines = [
            'Счёт на оплату № ' . $invoiceNo,
            'Заказ: ' . $order['number'],
            'Дата: ' . date('d.m.Y H:i', strtotime($order['createdAt'])),
            '',
            'Поставщик: ' . $company['legalName'],
            'ИНН: ' . $company['inn'],
            'Банк: ' . $company['bank'],
            'БИК: ' . $company['bik'],
            'Р/с: ' . $company['checkingAccount'],
            'К/с: ' . $company['correspondentAccount'],
            '',
            'Покупатель: ' . ($order['companyName'] ?: $order['name']),
            'Телефон: ' . $order['phone'],
            '',
        ];
        foreach ($order['items'] as $i) {
            $lines[] = '• ' . $i['name'] . ' × ' . $i['qty'] . ' = ' . Helpers::formatPrice($i['price'] * $i['qty']);
        }
        $lines[] = '';
        $lines[] = 'Итого к оплате: ' . Helpers::formatPrice($order['total']);
        $lines[] = 'В назначении платежа укажите: Оплата по счёту ' . $invoiceNo;
        if ($publicUrl) {
            $lines[] = 'Счёт онлайн: ' . $publicUrl;
        }
        return implode("\n", $lines);
    }
}
