<?php
declare(strict_types=1);

namespace App;

final class Api
{
    public static function handle(string $path): void
    {
        $method = Helpers::method();
        $parts = explode('/', trim($path, '/'));
        // path like api/auth/login
        array_shift($parts); // api
        $group = $parts[0] ?? '';
        $action = $parts[1] ?? '';

        if ($group === 'auth') {
            self::auth($action, $method);
        } elseif ($group === 'account') {
            self::account($method);
        } elseif ($group === 'orders' && $method === 'GET') {
            self::myOrders();
        } elseif ($group === 'order' && $method === 'POST') {
            self::createOrder();
        } elseif ($group === 'admin') {
            self::admin($action, $method);
        } else {
            Helpers::json(['ok' => false, 'error' => 'not_found'], 404);
        }
    }

    private static function auth(string $action, string $method): void
    {
        if ($action === 'captcha' && $method === 'GET') {
            Helpers::json(['ok' => true] + Captcha::create());
        }
        if ($action === 'me' && $method === 'GET') {
            $u = Auth::currentUser();
            Helpers::json(['ok' => true, 'user' => $u ? Auth::publicUser($u) : null]);
        }
        if ($action === 'logout' && $method === 'POST') {
            Auth::logout();
            Helpers::json(['ok' => true]);
        }
        if ($action === 'login' && $method === 'POST') {
            $body = Helpers::readJsonBody();
            $email = strtolower(trim((string) ($body['email'] ?? '')));
            $password = (string) ($body['password'] ?? '');
            $user = Auth::getByEmail($email);
            if (!$user || !Auth::verifyPassword($password, $user['password_hash'])) {
                Helpers::json(['ok' => false, 'error' => 'Неверный email или пароль'], 401);
            }
            Auth::createSession($user['id']);
            Helpers::json(['ok' => true, 'user' => Auth::publicUser($user)]);
        }
        if ($action === 'register' && $method === 'POST') {
            $body = Helpers::readJsonBody();
            if (!Captcha::verify((string) ($body['captchaToken'] ?? ''), (string) ($body['captchaAnswer'] ?? ''))) {
                Helpers::json(['ok' => false, 'error' => 'Неверная проверка (капча). Обновите пример и попробуйте снова.'], 400);
            }
            $name = trim((string) ($body['name'] ?? ''));
            $email = strtolower(trim((string) ($body['email'] ?? '')));
            $phone = trim((string) ($body['phone'] ?? ''));
            $password = (string) ($body['password'] ?? '');
            $billing = Billing::normalize($body);
            if (!$name || !$email || !$phone || strlen($password) < 6) {
                Helpers::json(['ok' => false, 'error' => 'Заполните контакты. Пароль — не менее 6 символов.'], 400);
            }
            $billingError = Billing::validate($billing);
            if ($billingError) {
                Helpers::json(['ok' => false, 'error' => $billingError], 400);
            }
            if (Auth::getByEmail($email)) {
                Helpers::json(['ok' => false, 'error' => 'Пользователь с таким email уже есть. Войдите в кабинет.'], 409);
            }
            $user = [
                'id' => Auth::makeUserId($email),
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'password_hash' => Auth::hashPassword($password),
                'created_at' => gmdate('c'),
                'company_name' => $billing['companyName'],
                'inn' => $billing['inn'],
                'ogrn' => $billing['ogrn'],
                'bik' => $billing['bik'],
                'checking_account' => $billing['checkingAccount'],
            ];
            Auth::saveUser($user);
            Auth::createSession($user['id']);
            Helpers::json(['ok' => true, 'user' => Auth::publicUser($user)]);
        }
        Helpers::json(['ok' => false, 'error' => 'not_found'], 404);
    }

    private static function account(string $method): void
    {
        $user = Auth::currentUser();
        if (!$user) {
            Helpers::json(['ok' => false, 'error' => 'unauthorized'], 401);
        }
        if ($method !== 'PATCH') {
            Helpers::json(['ok' => false, 'error' => 'method'], 405);
        }
        $body = Helpers::readJsonBody();
        $billing = Billing::normalize($body);
        $billingError = Billing::validate($billing);
        if ($billingError) {
            Helpers::json(['ok' => false, 'error' => $billingError], 400);
        }
        $user['name'] = trim((string) ($body['name'] ?? $user['name']));
        $user['phone'] = trim((string) ($body['phone'] ?? $user['phone']));
        $user['company_name'] = $billing['companyName'];
        $user['inn'] = $billing['inn'];
        $user['ogrn'] = $billing['ogrn'];
        $user['bik'] = $billing['bik'];
        $user['checking_account'] = $billing['checkingAccount'];
        Auth::saveUser($user);
        Helpers::json(['ok' => true, 'user' => Auth::publicUser($user)]);
    }

    private static function myOrders(): void
    {
        $user = Auth::currentUser();
        if (!$user) {
            Helpers::json(['ok' => false, 'error' => 'unauthorized'], 401);
        }
        Helpers::json(['ok' => true, 'orders' => Orders::listByUser($user['id'])]);
    }

    private static function createOrder(): void
    {
        $body = Helpers::readJsonBody();
        $type = ($body['type'] ?? 'order') === 'contact' ? 'contact' : 'order';
        $user = Auth::currentUser();
        $userId = $user['id'] ?? null;
        if (!$userId && !empty($body['email'])) {
            $byEmail = Auth::getByEmail((string) $body['email']);
            $userId = $byEmail['id'] ?? null;
        }
        $billing = Billing::normalize($body);
        if ($type === 'order') {
            $err = Billing::validate($billing);
            if ($err) {
                Helpers::json(['ok' => false, 'error' => $err], 400);
            }
            $items = $body['items'] ?? [];
            if (!is_array($items) || !count($items)) {
                Helpers::json(['ok' => false, 'error' => 'Корзина пуста'], 400);
            }
        } else {
            $items = [];
        }
        $name = trim((string) ($body['name'] ?? ''));
        $phone = trim((string) ($body['phone'] ?? ''));
        $email = trim((string) ($body['email'] ?? ''));
        if (!$name || !$phone) {
            Helpers::json(['ok' => false, 'error' => 'Укажите имя и телефон'], 400);
        }
        $order = Orders::create([
            'type' => $type,
            'user_id' => $userId,
            'name' => $name,
            'phone' => $phone,
            'email' => $email,
            'comment' => trim((string) ($body['comment'] ?? '')),
            'topic' => $body['topic'] ?? null,
            'items' => $items,
            'company_name' => $billing['companyName'],
            'inn' => $billing['inn'],
            'ogrn' => $billing['ogrn'],
            'bik' => $billing['bik'],
            'checking_account' => $billing['checkingAccount'],
        ]);
        Mailer::notifyAndInvoice($order);
        $order = Orders::getById($order['id']);
        Helpers::json(['ok' => true, 'order' => $order]);
    }

    private static function admin(string $action, string $method): void
    {
        if ($action === 'login' && $method === 'POST') {
            $body = Helpers::readJsonBody();
            if (($body['action'] ?? '') === 'logout') {
                AdminAuth::logout();
                Helpers::json(['ok' => true]);
            }
            $ok = AdminAuth::login((string) ($body['login'] ?? ''), (string) ($body['password'] ?? ''));
            if (!$ok) {
                Helpers::json(['ok' => false, 'error' => 'Неверный логин или пароль'], 401);
            }
            Helpers::json(['ok' => true]);
        }

        AdminAuth::require();

        if ($action === 'orders') {
            if ($method === 'GET') {
                Helpers::json(['ok' => true, 'orders' => Orders::listAll()]);
            }
            if ($method === 'PATCH') {
                $body = Helpers::readJsonBody();
                $id = (string) ($body['id'] ?? '');
                if ($id === '') {
                    Helpers::json(['ok' => false, 'error' => 'bad_request'], 400);
                }
                $patch = [];
                foreach (['status','name','phone','email','comment','companyName','items'] as $k) {
                    if (array_key_exists($k, $body)) {
                        $patch[$k] = $body[$k];
                    }
                }
                $order = Orders::update($id, $patch);
                if (!$order) {
                    Helpers::json(['ok' => false, 'error' => 'not_found'], 404);
                }
                Helpers::json(['ok' => true, 'order' => $order]);
            }
            if ($method === 'DELETE') {
                $body = Helpers::readJsonBody();
                $ok = Orders::delete((string) ($body['id'] ?? ''));
                Helpers::json(['ok' => $ok], $ok ? 200 : 404);
            }
        }

        if ($action === 'users') {
            if ($method === 'GET') {
                Helpers::json(['ok' => true, 'users' => Users::listAll()]);
            }
            if ($method === 'DELETE') {
                $body = Helpers::readJsonBody();
                $ok = Users::delete((string) ($body['id'] ?? ''));
                Helpers::json(['ok' => $ok], $ok ? 200 : 404);
            }
        }

        if ($action === 'products') {
            if ($method === 'GET') {
                $q = trim((string) ($_GET['q'] ?? ''));
                $items = Catalog::getProducts($q ?: null, null, null, $q ? 80 : 40);
                $out = [];
                foreach ($items as $p) {
                    $p['hasOverride'] = Catalog::hasOverride($p['id']);
                    $out[] = $p;
                }
                Helpers::json(['ok' => true, 'products' => $out]);
            }
            if ($method === 'PATCH') {
                $body = Helpers::readJsonBody();
                $id = (string) ($body['id'] ?? '');
                try {
                    $product = Catalog::updateProduct($id, $body);
                } catch (\InvalidArgumentException $e) {
                    Helpers::json(['ok' => false, 'error' => $e->getMessage()], 400);
                }
                if (!$product) {
                    Helpers::json(['ok' => false, 'error' => 'not_found'], 404);
                }
                Helpers::json(['ok' => true, 'product' => $product]);
            }
        }

        Helpers::json(['ok' => false, 'error' => 'not_found'], 404);
    }
}
