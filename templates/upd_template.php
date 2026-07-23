<?php
/** @var array $v */
declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="utf-8" />
<title>УПД № <?= \App\Helpers::e($v['no']) ?> от <?= \App\Helpers::e($v['shortDate']) ?> УПД (Статус 2)</title>
<style><?= $v['style'] ?></style>
</head>
<body>
<div class="admin-bar no-print">
  Только для администратора · УПД (Статус 2) № <?= \App\Helpers::e($v['no']) ?> от <?= \App\Helpers::e($v['shortDate']) ?>
  · <a href="javascript:window.print()">Печать</a> · заказ <?= \App\Helpers::e($v['orderNumber']) ?>
</div>

<div class="sheet">
  <table class="head-table">
    <tr>
      <td class="head-left">
        <div class="upd-title">Универсальный<br />передаточный<br />документ</div>
        <div class="status-line">
          <span>Статус:</span>
          <span class="status-box">2</span>
        </div>
        <div class="status-help">
          1&nbsp;-&nbsp;счет-фактура&nbsp;и<br />
          передаточный<br />
          документ&nbsp;(акт)<br />
          2&nbsp;-&nbsp;передаточный<br />
          документ&nbsp;(акт)
        </div>
      </td>
      <td class="head-right">
        <div class="law">
          Приложение № 1 к постановлению Правительства Российской Федерации от 26 декабря 2011 г. № 1137<br />
          (в ред. Постановления Правительства РФ от 23 января 2026 г. № 26)
        </div>

        <div class="sf-line">
          Счет-фактура № <span class="ul"><?= \App\Helpers::e($v['no']) ?></span>
          от <span class="ul ul-wide"><?= \App\Helpers::e($v['longDate']) ?></span>
          <span class="code">(1)</span>
        </div>
        <div class="sf-line">
          Исправление № <span class="ul">--</span>
          от <span class="ul ul-wide">--</span>
          <span class="code">(1а)</span>
        </div>

        <table class="field-table">
          <tr>
            <td class="lbl">Продавец:</td>
            <td class="val"><?= \App\Helpers::e($v['sellerName']) ?></td>
            <td class="code">(2)</td>
          </tr>
          <tr class="spacer"><td colspan="3"></td></tr>
          <tr>
            <td class="lbl">Адрес:</td>
            <td class="val"><?= \App\Helpers::e($v['sellerAddress']) ?></td>
            <td class="code">(2а)</td>
          </tr>
          <tr class="spacer"><td colspan="3"></td></tr>
          <tr>
            <td class="lbl">ИНН/КПП продавца:</td>
            <td class="val"><?= \App\Helpers::e($v['sellerInnKpp']) ?></td>
            <td class="code">(2б)</td>
          </tr>
          <tr class="spacer"><td colspan="3"></td></tr>
          <tr>
            <td class="lbl">Грузоотправитель и его адрес:</td>
            <td class="val">он же</td>
            <td class="code">(3)</td>
          </tr>
          <tr class="spacer"><td colspan="3"></td></tr>
          <tr>
            <td class="lbl">Грузополучатель и его адрес:</td>
            <td class="val"><?= \App\Helpers::e($v['consignee']) ?></td>
            <td class="code">(4)</td>
          </tr>
          <tr class="spacer"><td colspan="3"></td></tr>
          <tr>
            <td class="lbl">К платежно-расчетному документу №</td>
            <td class="val">--</td>
            <td class="code">(5)</td>
          </tr>
          <tr class="spacer"><td colspan="3"></td></tr>
          <tr>
            <td class="lbl">Документ об отгрузке:</td>
            <td class="val">Универсальный передаточный документ, № <?= \App\Helpers::e($v['no']) ?> от <?= \App\Helpers::e($v['shortDate']) ?> г.</td>
            <td class="code">(5а)</td>
          </tr>
        </table>

        <div class="field-5b">
          К счету-фактуре (счетам-фактурам), выставленному (выставленным) при получении оплаты, частичной оплаты или иных платежей в счет предстоящих поставок товаров
          (выполнения работ, оказания услуг), передачи имущественных прав № <span class="ul">&nbsp;</span> от <span class="ul">&nbsp;</span>,
          исправление № <span class="ul">&nbsp;</span> от <span class="ul">&nbsp;</span>
          <span class="code">(5б)</span>
        </div>

        <table class="field-table">
          <tr>
            <td class="lbl">Покупатель:</td>
            <td class="val"><?= \App\Helpers::e($v['buyerName']) ?></td>
            <td class="code">(6)</td>
          </tr>
          <tr class="spacer"><td colspan="3"></td></tr>
          <tr>
            <td class="lbl">Адрес:</td>
            <td class="val"><?= \App\Helpers::e($v['buyerAddress']) ?></td>
            <td class="code">(6а)</td>
          </tr>
          <tr class="spacer"><td colspan="3"></td></tr>
          <tr>
            <td class="lbl">ИНН/КПП покупателя:</td>
            <td class="val"><?= \App\Helpers::e($v['buyerInnKpp']) ?></td>
            <td class="code">(6б)</td>
          </tr>
          <tr class="spacer"><td colspan="3"></td></tr>
          <tr>
            <td class="lbl">Валюта: наименование, код:</td>
            <td class="val">Российский рубль, 643</td>
            <td class="code">(7)</td>
          </tr>
          <tr class="spacer"><td colspan="3"></td></tr>
          <tr>
            <td class="lbl">Идентификатор государственного контракта, договора (соглашения) (при наличии):</td>
            <td class="val">&nbsp;</td>
            <td class="code">(8)</td>
          </tr>
        </table>
      </td>
    </tr>
  </table>

  <table class="goods">
    <thead>
      <tr>
        <th rowspan="2" class="col-a">Код товара/<br />работ, услуг</th>
        <th rowspan="2" class="col-1">№<br />п/п</th>
        <th rowspan="2" class="col-1a name-col">Наименование товара (описание выполненных работ, оказанных услуг), имущественного права</th>
        <th rowspan="2" class="col-1b">Код вида<br />товара</th>
        <th colspan="2">Единица<br />измерения</th>
        <th rowspan="2" class="col-3">Коли-<br />чество<br />(объем)</th>
        <th rowspan="2" class="col-4">Цена (тариф)<br />за единицу<br />измерения</th>
        <th rowspan="2" class="col-5">Стоимость<br />товаров (работ,<br />услуг), имуще-<br />ственных прав<br />без налога —<br />всего</th>
        <th rowspan="2" class="col-6">В том<br />числе<br />сумма<br />акциза</th>
        <th rowspan="2" class="col-7">Нало-<br />вая<br />ставка</th>
        <th rowspan="2" class="col-8">Сумма<br />налога,<br />предъяв-<br />ляемая<br />покупателю</th>
        <th rowspan="2" class="col-9">Стоимость<br />товаров (работ,<br />услуг), имуще-<br />ственных прав<br />с налогом —<br />всего</th>
        <th colspan="2">Страна<br />происхождения<br />товара</th>
        <th rowspan="2" class="col-11">Регистрационный<br />номер декларации<br />на товары или<br />регистрационный<br />номер партии<br />товара, подлежа-<br />щего просле-<br />живаемости</th>
      </tr>
      <tr>
        <th class="col-2">код</th>
        <th class="col-2a">условное<br />обозна-<br />чение<br />(нацио-<br />нальное)</th>
        <th class="col-10">циф-<br />ровой<br />код</th>
        <th class="col-10a">краткое<br />наиме-<br />нование</th>
      </tr>
      <tr class="codes">
        <th>А</th><th>1</th><th>1а</th><th>1б</th><th>2</th><th>2а</th><th>3</th><th>4</th><th>5</th><th>6</th><th>7</th><th>8</th><th>9</th><th>10</th><th>10а</th><th>11</th>
      </tr>
    </thead>
    <tbody>
      <?= $v['itemRows'] ?>
      <tr class="total-row">
        <td colspan="8" class="r">Всего к оплате (9)</td>
        <td class="r"><?= \App\Helpers::e($v['total']) ?></td>
        <td class="c">X</td>
        <td></td>
        <td class="c">без НДС</td>
        <td class="r"><?= \App\Helpers::e($v['total']) ?></td>
        <td colspan="3"></td>
      </tr>
    </tbody>
  </table>

  <table class="sign-block">
    <tr>
      <td class="sheet-note" rowspan="2">Документ<br />составлен<br />на<br />1 листе</td>
      <td class="role">Руководитель организации<br />или иное уполномоченное лицо</td>
      <td class="role">Главный бухгалтер<br />или иное уполномоченное лицо</td>
      <td class="role">Индивидуальный предприниматель<br />или иное уполномоченное лицо</td>
    </tr>
    <tr>
      <td>
        <div class="sign-row"><span>(подпись)</span><span>(ф.и.о.)</span></div>
        <div class="name-line"><?= \App\Helpers::e($v['signer']) ?></div>
      </td>
      <td>
        <div class="sign-row"><span>(подпись)</span><span>(ф.и.о.)</span></div>
        <div class="name-line">&nbsp;</div>
      </td>
      <td>
        <div class="sign-row"><span>(подпись)</span><span>(ф.и.о.)</span></div>
        <div class="name-line"><?= \App\Helpers::e($v['signer']) ?></div>
        <div class="ogrnip-note">(основной государственный регистрационный номер индивидуального предпринимателя и дата присвоения такого номера)</div>
      </td>
    </tr>
  </table>

  <table class="footer-section">
    <tr>
      <td class="lbl">Основание передачи (сдачи) / получения (приемки)</td>
      <td class="val">Счет № <?= \App\Helpers::e($v['invoiceNo']) ?> от <?= \App\Helpers::e($v['shortDate']) ?></td>
      <td class="code">(8)</td>
    </tr>
    <tr><td colspan="3" class="hint">(договор; доверенность и др.)</td></tr>
  </table>

  <table class="footer-section">
    <tr>
      <td class="lbl">Данные о транспортировке и грузе</td>
      <td class="val">&nbsp;</td>
      <td class="code">(9)</td>
    </tr>
    <tr><td colspan="3" class="hint">(транспортная накладная, поручение экспедитору, экспедиторская / складская расписка и др. / масса нетто/ брутто груза, если не приведены ссылки на транспортные документы, содержащие эти сведения)</td></tr>
  </table>

  <table class="footer-cols">
    <tr>
      <td>
        <table class="footer-box">
          <tr><td class="title">Товар (груз) передал / услуги, результаты работ, права сдал <span class="code">(10)</span></td></tr>
          <tr>
            <td>
              <table style="width:100%;"><tr>
                <td style="width:33%;"><div class="line"><?= \App\Helpers::e($v['signer']) ?></div><div class="sub"><span>(ф.и.о.)</span></div></td>
                <td style="width:33%;"><div class="line">Директор</div><div class="sub"><span>(должность)</span></div></td>
                <td style="width:33%;"><div class="line">&nbsp;</div><div class="sub"><span>(подпись)</span></div></td>
              </tr></table>
            </td>
          </tr>
          <tr><td style="padding-top:3px;">Дата отгрузки, передачи (сдачи) <span class="code">(11)</span> <strong><?= \App\Helpers::e($v['quotedDate']) ?></strong></td></tr>
          <tr><td class="title" style="padding-top:3px;">Иные сведения об отгрузке, передаче <span class="code">(12)</span></td></tr>
          <tr><td class="hint">(ссылки на неотъемлемые приложения, сопутствующие документы, иные документы и т.п.)</td></tr>
          <tr><td><div class="line">&nbsp;</div></td></tr>
          <tr><td class="title" style="padding-top:3px;">Ответственный за правильность оформления факта хозяйственной жизни <span class="code">(13)</span></td></tr>
          <tr>
            <td>
              <table style="width:100%;"><tr>
                <td style="width:33%;"><div class="line"><?= \App\Helpers::e($v['signer']) ?></div><div class="sub"><span>(ф.и.о.)</span></div></td>
                <td style="width:33%;"><div class="line">Директор</div><div class="sub"><span>(должность)</span></div></td>
                <td style="width:33%;"><div class="line">&nbsp;</div><div class="sub"><span>(подпись)</span></div></td>
              </tr></table>
            </td>
          </tr>
          <tr><td class="title" style="padding-top:3px;">Наименование экономического субъекта — составителя документа (в т.ч. комиссионера / агента) <span class="code">(14)</span></td></tr>
          <tr><td><div class="entity"><?= \App\Helpers::e($v['sellerName']) ?>, ИНН/КПП <?= \App\Helpers::e($v['sellerInnKpp']) ?></div></td></tr>
          <tr><td class="entity-hint">(может не заполняться при проставлении печати в М.П., может быть указан ИНН / КПП)</td></tr>
          <tr><td class="mp">М.П.</td></tr>
        </table>
      </td>
      <td>
        <table class="footer-box">
          <tr><td class="title">Товар (груз) получил / услуги, результаты работ, права принял <span class="code">(15)</span></td></tr>
          <tr>
            <td>
              <table style="width:100%;"><tr>
                <td style="width:33%;"><div class="line">&nbsp;</div><div class="sub"><span>(ф.и.о.)</span></div></td>
                <td style="width:33%;"><div class="line">&nbsp;</div><div class="sub"><span>(должность)</span></div></td>
                <td style="width:33%;"><div class="line">&nbsp;</div><div class="sub"><span>(подпись)</span></div></td>
              </tr></table>
            </td>
          </tr>
          <tr><td style="padding-top:3px;">Дата получения (приемки) <span class="code">(16)</span></td></tr>
          <tr><td><div class="line">&nbsp;</div></td></tr>
          <tr><td class="title" style="padding-top:3px;">Иные сведения о получении, приемке <span class="code">(17)</span></td></tr>
          <tr><td class="hint">(информация о наличии/отсутствии претензии; ссылки на неотъемлемые приложения, и другие документы и т.п.)</td></tr>
          <tr><td><div class="line">&nbsp;</div></td></tr>
          <tr><td class="title" style="padding-top:3px;">Ответственный за правильность оформления факта хозяйственной жизни <span class="code">(18)</span></td></tr>
          <tr>
            <td>
              <table style="width:100%;"><tr>
                <td style="width:33%;"><div class="line">&nbsp;</div><div class="sub"><span>(ф.и.о.)</span></div></td>
                <td style="width:33%;"><div class="line">&nbsp;</div><div class="sub"><span>(должность)</span></div></td>
                <td style="width:33%;"><div class="line">&nbsp;</div><div class="sub"><span>(подпись)</span></div></td>
              </tr></table>
            </td>
          </tr>
          <tr><td class="title" style="padding-top:3px;">Наименование экономического субъекта — составителя документа <span class="code">(19)</span></td></tr>
          <tr><td><div class="entity">&nbsp;</div></td></tr>
          <tr><td class="entity-hint">(может не заполняться при проставлении печати в М.П., может быть указан ИНН / КПП)</td></tr>
          <tr><td class="mp">М.П.</td></tr>
        </table>
      </td>
    </tr>
  </table>
</div>
</body>
</html>
