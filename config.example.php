<?php
/**
 * Скопируйте в config.php и заполните значения.
 * config.php не коммитьте в git.
 */
return [
    'site_url' => 'http://localhost:8080',
    'order_to_email' => '3822380@mail.ru',
    'admin_login' => 'admin1',
    'admin_password' => '413962',
    'captcha_secret' => '',

    'company' => [
        'legal_name' => 'ИП Котович Ольга Александровна',
        'inn' => '667101408379',
        'kpp' => '',
        'ogrnip' => '317665800091944',
        'address' => '620149, г. Екатеринбург, ул. Серафимы Дерябиной 47-72',
        'bank' => 'АО "ТИНЬКОФФ БАНК"',
        'bik' => '044525974',
        'rs' => '40802810200000158645',
        'ks' => '30101810145250000974',
    ],

    'smtp' => [
        'host' => '',
        'port' => 465,
        'secure' => true,
        'user' => '',
        'pass' => '',
    ],

    'telegram' => [
        'bot_token' => '',
        'chat_id' => '',
    ],
];
