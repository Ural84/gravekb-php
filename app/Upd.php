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
        return ['января','февраля','марта','апреля','мая','июня','июля','августа','сентября','октября','ноября','декабря'];
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

    public static function buildHtml(array $order): string
    {
        $company = Config::company();
        $site = Config::site();
        $no = self::numberFromOrder($order);
        $invoiceNo = $order['invoiceNumber'] ?: Invoice::numberFromOrder($order);
        $ts = strtotime($order['createdAt']);
        $months = self::months();
        $longDate = date('j', $ts) . ' ' . $months[(int) date('n', $ts) - 1] . ' ' . date('Y', $ts) . ' г.';
        $shortDate = date('d.m.Y', $ts);
        $quotedDate = '"' . date('j', $ts) . '" ' . $months[(int) date('n', $ts) - 1] . ' ' . date('Y', $ts) . ' г.';
        $sellerInnKpp = $company['inn'] . '/' . ($company['kpp'] ?: '');
        $sellerAddress = $company['address'] ?: $site['address'];
        $buyerName = $order['companyName'] ?: $order['name'];
        $buyerInnKpp = $order['inn'] ? ($order['inn'] . '/') : '/';
        $signer = self::shortPerson($company['legalName']);
        $style = file_get_contents(Config::root() . '/templates/upd_styles.css') ?: '';

        $itemRows = '';
        foreach ($order['items'] as $index => $item) {
            $sum = $item['price'] * $item['qty'];
            $itemRows .= '<tr><td class="c">--</td><td class="c">' . ($index + 1) . '</td><td class="l">'
                . Helpers::e($item['name']) . '</td><td class="c">--</td><td class="c">796</td><td class="c">шт</td><td class="r">'
                . (int) $item['qty'] . '</td><td class="r">' . self::money((float) $item['price']) . '</td><td class="r">'
                . self::money((float) $sum) . '</td><td class="c">без акциза</td><td class="c">без НДС</td><td class="c">без НДС</td><td class="r">'
                . self::money((float) $sum) . '</td><td class="c">--</td><td class="c">--</td><td class="c">--</td></tr>';
        }
        if (!$order['items']) {
            $itemRows = '<tr><td class="c">--</td><td class="c">1</td><td class="l">&nbsp;</td><td class="c">--</td><td class="c">--</td><td class="c">--</td><td class="c">--</td><td class="c">--</td><td class="r">0,00</td><td class="c">без акциза</td><td class="c">без НДС</td><td class="c">без НДС</td><td class="r">0,00</td><td class="c">--</td><td class="c">--</td><td class="c">--</td></tr>';
        }

        return '<!DOCTYPE html><html lang="ru"><head><meta charset="utf-8" /><title>УПД № '
            . Helpers::e($no) . ' от ' . Helpers::e($shortDate) . ' УПД (Статус 2)</title><style>'
            . $style . '</style></head><body>'
            . '<div class="admin-bar no-print">Только для администратора · УПД (Статус 2) № ' . Helpers::e($no)
            . ' от ' . Helpers::e($shortDate) . ' · <a href="javascript:window.print()">Печать</a> · заказ '
            . Helpers::e($order['number']) . '</div>'
            . '<div class="sheet"><div class="law">Приложение № 1 к постановлению Правительства Российской Федерации от 26 декабря 2011 г. № 1137 (в ред. Постановления Правительства РФ от 23 января 2026 г. № 26)</div>'
            . '<table class="head"><tr><td class="left-col"><div class="title-stack">Универсальный<br/>передаточный<br/>документ</div>'
            . '<div class="status-row"><span class="status-label">Статус:</span><span class="status-box">2</span></div>'
            . '<div class="status-help">1 - счет-фактура и<br/>передаточный<br/>документ (акт)<br/>2 - передаточный<br/>документ (акт)</div></td><td>'
            . '<div class="sf-line">Счет-фактура № <span class="num">' . Helpers::e($no) . '</span> от <span class="date">'
            . Helpers::e($longDate) . '</span> <span class="mark">(1)</span></div>'
            . '<div class="sf-line">Исправление № <span class="num">--</span> от <span class="date">--</span> <span class="mark">(1а)</span></div>'
            . '<table class="party">'
            . '<tr><td class="lbl">Продавец:</td><td class="val">' . Helpers::e($company['legalName']) . '</td><td class="code">(2)</td></tr>'
            . '<tr><td class="lbl">Адрес:</td><td class="val">' . Helpers::e($sellerAddress) . '</td><td class="code">(2а)</td></tr>'
            . '<tr><td class="lbl">ИНН/КПП продавца:</td><td class="val">' . Helpers::e($sellerInnKpp) . '</td><td class="code">(2б)</td></tr>'
            . '<tr><td class="lbl">Грузоотправитель и его адрес:</td><td class="val">он же</td><td class="code">(3)</td></tr>'
            . '<tr><td class="lbl">Грузополучатель и его адрес:</td><td class="val">' . Helpers::e($buyerName) . '</td><td class="code">(4)</td></tr>'
            . '<tr><td class="lbl">К платежно-расчетному документу №</td><td class="val">--</td><td class="code">(5)</td></tr>'
            . '<tr><td class="lbl">Документ об отгрузке:</td><td class="val">Универсальный передаточный документ, № '
            . Helpers::e($no) . ' от ' . Helpers::e($shortDate) . ' г.</td><td class="code">(5а)</td></tr></table>'
            . '<table class="party" style="margin-top:4px;">'
            . '<tr><td class="lbl">Покупатель:</td><td class="val">' . Helpers::e($buyerName) . '</td><td class="code">(6)</td></tr>'
            . '<tr><td class="lbl">Адрес:</td><td class="val">&nbsp;</td><td class="code">(6а)</td></tr>'
            . '<tr><td class="lbl">ИНН/КПП покупателя:</td><td class="val">' . Helpers::e($buyerInnKpp) . '</td><td class="code">(6б)</td></tr>'
            . '<tr><td class="lbl">Валюта: наименование, код:</td><td class="val">Российский рубль, 643</td><td class="code">(7)</td></tr>'
            . '</table></td></tr></table>'
            . '<table class="goods"><thead><tr>'
            . '<th>Код</th><th>№</th><th>Наименование</th><th>Код вида</th><th>Код ед.</th><th>Ед.</th><th>Кол-во</th><th>Цена</th><th>Сумма без налога</th><th>Акциз</th><th>Ставка</th><th>Налог</th><th>Сумма с налогом</th><th>Страна</th><th>Наим.</th><th>Декларация</th>'
            . '</tr></thead><tbody>' . $itemRows
            . '<tr class="total-row"><td colspan="8" class="r">Всего к оплате (9)</td><td class="r">' . self::money((float) $order['total'])
            . '</td><td class="c">X</td><td></td><td class="c">без НДС</td><td class="r">' . self::money((float) $order['total'])
            . '</td><td colspan="3"></td></tr></tbody></table>'
            . '<table class="block"><tr><td style="width:110px;" class="strong">Документ<br/>составлен на<br/>1 листе</td><td>'
            . '<div class="strong">Индивидуальный предприниматель<br/>или иное уполномоченное лицо</div>'
            . '<div class="uline">' . Helpers::e($signer) . '</div><div class="cap">(ф.и.о.)</div>'
            . '<div class="uline">' . Helpers::e($company['ogrnip'] ?: '') . '</div><div class="cap">(ОГРНИП)</div></td></tr></table>'
            . '<table class="party" style="margin-top:8px;"><tr><td class="lbl">Основание передачи</td><td class="val">Счет № '
            . Helpers::e($invoiceNo) . ' от ' . Helpers::e($shortDate) . '</td><td class="code">(8)</td></tr></table>'
            . '<div style="margin-top:8px;">Дата отгрузки <span class="strong">' . Helpers::e($quotedDate) . '</span></div>'
            . '<div class="uline">' . Helpers::e($company['legalName']) . ', ИНН/КПП ' . Helpers::e($sellerInnKpp) . '</div>'
            . '<div class="mp">М.П.</div></div></body></html>';
    }
}
