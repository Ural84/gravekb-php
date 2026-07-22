<?php
declare(strict_types=1);

namespace App;

final class Mailer
{
    private static function orderText(array $order): string
    {
        $lines = [
            'Заявка № ' . $order['number'],
            'Тип: ' . ($order['type'] === 'contact' ? 'обратная связь' : 'заказ'),
            'Имя: ' . $order['name'],
            'Телефон: ' . $order['phone'],
            $order['email'] ? 'Email: ' . $order['email'] : '',
            $order['comment'] ? 'Комментарий: ' . $order['comment'] : '',
            '',
        ];
        foreach ($order['items'] as $i) {
            $lines[] = '• ' . $i['name'] . ' × ' . $i['qty'] . ' = ' . ($i['price'] * $i['qty']) . ' ₽';
        }
        if ($order['total']) {
            $lines[] = 'Итого: ' . $order['total'] . ' ₽';
        }
        return implode("\n", array_filter($lines, fn($l) => $l !== null));
    }

    public static function notifyAndInvoice(array $order): array
    {
        $site = Config::site();
        $notified = self::telegram($order) || self::smtpOwner($order) || self::formSubmit($order);
        if (!$notified) {
            error_log("ORDER\n" . self::orderText($order));
        }
        $invoiceSent = false;
        if ($order['type'] === 'order' && $order['email']) {
            $invoiceSent = self::smtpInvoice($order);
        }
        if ($notified || $invoiceSent) {
            Orders::update($order['id'], [
                'emailSent' => true,
                'invoiceSent' => $invoiceSent,
            ]);
        }
        return ['notified' => $notified, 'invoiceSent' => $invoiceSent];
    }

    private static function telegram(array $order): bool
    {
        $tg = Config::get('telegram', []);
        $token = $tg['bot_token'] ?? '';
        $chat = $tg['chat_id'] ?? '';
        if (!$token || !$chat) {
            return false;
        }
        $site = Config::site();
        $payload = json_encode([
            'chat_id' => $chat,
            'text' => "🔔 Новая заявка в {$site['name']}\n\n" . self::orderText($order),
        ], JSON_UNESCAPED_UNICODE);
        $ctx = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\n",
                'content' => $payload,
                'timeout' => 8,
            ],
        ]);
        $res = @file_get_contents("https://api.telegram.org/bot{$token}/sendMessage", false, $ctx);
        return $res !== false;
    }

    private static function smtpOwner(array $order): bool
    {
        $smtp = Config::get('smtp', []);
        if (empty($smtp['host']) || empty($smtp['user']) || empty($smtp['pass'])) {
            return false;
        }
        $site = Config::site();
        $to = Config::get('order_to_email', $site['email']);
        return self::sendMail(
            $smtp,
            $to,
            "[{$site['name']}] Заявка {$order['number']}",
            self::orderText($order),
            null,
            $order['email'] ?: null
        );
    }

    private static function formSubmit(array $order): bool
    {
        $site = Config::site();
        $to = Config::get('order_to_email', $site['email']);
        $payload = json_encode([
            'name' => $order['name'],
            'email' => $order['email'] ?: $site['email'],
            'message' => self::orderText($order),
            '_subject' => "Заявка {$order['number']}",
        ], JSON_UNESCAPED_UNICODE);
        $ctx = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\nAccept: application/json\r\n",
                'content' => $payload,
                'timeout' => 8,
            ],
        ]);
        $res = @file_get_contents('https://formsubmit.co/ajax/' . rawurlencode((string) $to), false, $ctx);
        return $res !== false;
    }

    private static function smtpInvoice(array $order): bool
    {
        $smtp = Config::get('smtp', []);
        if (empty($smtp['host']) || empty($smtp['user']) || empty($smtp['pass'])) {
            return false;
        }
        $url = Config::siteUrl() . '/invoice/' . $order['id'];
        $html = Invoice::buildHtml($order, $url);
        $text = Invoice::buildText($order, $url);
        $site = Config::site();
        $no = $order['invoiceNumber'] ?: Invoice::numberFromOrder($order);
        return self::sendMail(
            $smtp,
            $order['email'],
            "Счёт {$no} — {$site['name']}",
            $text,
            $html
        );
    }

    private static function sendMail(array $smtp, string $to, string $subject, string $text, ?string $html = null, ?string $replyTo = null): bool
    {
        // Minimal SMTP via PHP mail() fallback if sockets fail; prefer socket SMTP
        $host = $smtp['host'];
        $port = (int) ($smtp['port'] ?? 465);
        $user = $smtp['user'];
        $pass = $smtp['pass'];
        $secure = !empty($smtp['secure']);
        $site = Config::site();
        $from = $user;

        $errno = 0;
        $errstr = '';
        $remote = ($secure ? 'ssl://' : '') . $host . ':' . $port;
        $fp = @stream_socket_client($remote, $errno, $errstr, 12);
        if (!$fp) {
            $headers = "From: {$site['name']} <{$from}>\r\nContent-Type: text/plain; charset=UTF-8\r\n";
            if ($replyTo) {
                $headers .= "Reply-To: {$replyTo}\r\n";
            }
            return @mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=', $text, $headers);
        }
        stream_set_timeout($fp, 12);
        $read = fn() => fgets($fp, 515);
        $cmd = function (string $c) use ($fp, $read) {
            fwrite($fp, $c . "\r\n");
            return $read();
        };
        $read();
        $cmd('EHLO localhost');
        $cmd('AUTH LOGIN');
        $cmd(base64_encode($user));
        $cmd(base64_encode($pass));
        $cmd('MAIL FROM:<' . $from . '>');
        $cmd('RCPT TO:<' . $to . '>');
        $cmd('DATA');
        $boundary = 'b' . bin2hex(random_bytes(8));
        $body = "From: {$site['name']} <{$from}>\r\n";
        $body .= "To: <{$to}>\r\n";
        if ($replyTo) {
            $body .= "Reply-To: <{$replyTo}>\r\n";
        }
        $body .= 'Subject: =?UTF-8?B?' . base64_encode($subject) . "?=\r\n";
        $body .= "MIME-Version: 1.0\r\n";
        if ($html) {
            $body .= "Content-Type: multipart/alternative; boundary=\"{$boundary}\"\r\n\r\n";
            $body .= "--{$boundary}\r\nContent-Type: text/plain; charset=UTF-8\r\n\r\n{$text}\r\n";
            $body .= "--{$boundary}\r\nContent-Type: text/html; charset=UTF-8\r\n\r\n{$html}\r\n";
            $body .= "--{$boundary}--\r\n";
        } else {
            $body .= "Content-Type: text/plain; charset=UTF-8\r\n\r\n{$text}\r\n";
        }
        $body .= ".\r\n";
        fwrite($fp, $body);
        $read();
        $cmd('QUIT');
        fclose($fp);
        return true;
    }
}
