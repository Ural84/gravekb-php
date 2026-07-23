<?php
declare(strict_types=1);

namespace App;

final class Upd
{
    private static function money(float $v): string
    {
        return number_format($v, 2, ',', ' ');
    }

    private static function months(): array
    {
        return ['января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'];
    }

    private static function shortPerson(string $legalName): string
    {
        $cleaned = trim(preg_replace('/^ИП\s+/iu', '', $legalName) ?? $legalName);
        $parts = preg_split('/\s+/', $cleaned) ?: [];
        if (count($parts) >= 3) {
            return $parts[0] . ' ' . mb_substr($parts[1], 0, 1) . '. ' . mb_substr($parts[2], 0, 1) . '.';
        }
        if (count($parts) === 2) {
            return $parts[0] . ' ' . mb_substr($parts[1], 0, 1) . '.';
        }
        return $cleaned ?: $legalName;
    }

    public static function numberFromOrder(array $order): string
    {
        return $order['updNumber'] ?: ($order['invoiceNumber'] ?: Invoice::numberFromOrder($order));
    }

    private static function itemRows(array $items): string
    {
        if (!$items) {
            return '<tr>'
                . '<td class="c">--</td><td class="c">1</td><td class="l">&nbsp;</td><td class="c">--</td>'
                . '<td class="c">--</td><td class="c">--</td><td class="c">--</td><td class="c">--</td>'
                . '<td class="r">0,00</td><td class="c">без акциза</td><td class="c">без НДС</td><td class="c">без НДС</td>'
                . '<td class="r">0,00</td><td class="c">--</td><td class="c">--</td><td class="c">--</td>'
                . '</tr>';
        }

        $rows = '';
        foreach ($items as $index => $item) {
            $sum = $item['price'] * $item['qty'];
            $rows .= '<tr>'
                . '<td class="c">--</td>'
                . '<td class="c">' . ($index + 1) . '</td>'
                . '<td class="l">' . Helpers::e($item['name']) . '</td>'
                . '<td class="c">--</td>'
                . '<td class="c">796</td>'
                . '<td class="c">шт</td>'
                . '<td class="r">' . (int) $item['qty'] . '</td>'
                . '<td class="r">' . self::money((float) $item['price']) . '</td>'
                . '<td class="r">' . self::money((float) $sum) . '</td>'
                . '<td class="c">без акциза</td>'
                . '<td class="c">без НДС</td>'
                . '<td class="c">без НДС</td>'
                . '<td class="r">' . self::money((float) $sum) . '</td>'
                . '<td class="c">--</td>'
                . '<td class="c">--</td>'
                . '<td class="c">--</td>'
                . '</tr>';
        }
        return $rows;
    }

    public static function buildHtml(array $order): string
    {
        $company = Config::company();
        $no = self::numberFromOrder($order);
        $invoiceNo = $order['invoiceNumber'] ?: Invoice::numberFromOrder($order);
        $ts = strtotime($order['createdAt']);
        $months = self::months();
        $longDate = date('j', $ts) . ' ' . $months[(int) date('n', $ts) - 1] . ' ' . date('Y', $ts) . ' г.';
        $shortDate = date('d.m.Y', $ts);
        $quotedDate = '"' . date('j', $ts) . '" ' . $months[(int) date('n', $ts) - 1] . ' ' . date('Y', $ts) . ' г.';
        $sellerInnKpp = $company['inn'] . '/' . ($company['kpp'] ?: '');
        $sellerAddress = $company['address'] ?: Config::site()['address'];
        $buyerName = $order['companyName'] ?: $order['name'] ?: '';
        $buyerInnKpp = $order['inn'] ? ($order['inn'] . '/') : '/';
        $consignee = '--';
        $signer = self::shortPerson($company['legalName']);
        $style = file_get_contents(Config::root() . '/templates/upd_styles.css') ?: '';

        $v = [
            'no' => $no,
            'invoiceNo' => $invoiceNo,
            'orderNumber' => $order['number'],
            'shortDate' => $shortDate,
            'longDate' => $longDate,
            'quotedDate' => $quotedDate,
            'sellerName' => $company['legalName'],
            'sellerAddress' => $sellerAddress,
            'sellerInnKpp' => $sellerInnKpp,
            'buyerName' => $buyerName,
            'buyerAddress' => '',
            'buyerInnKpp' => $buyerInnKpp,
            'consignee' => $consignee,
            'signer' => $signer,
            'total' => self::money((float) $order['total']),
            'itemRows' => self::itemRows($order['items']),
            'style' => $style,
        ];

        ob_start();
        require Config::root() . '/templates/upd_template.php';
        return (string) ob_get_clean();
    }
}
