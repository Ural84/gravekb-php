<?php
declare(strict_types=1);

namespace App;

final class Router
{
    public static function dispatch(string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $path = rtrim($path, '/') ?: '/';

        if (str_starts_with($path, '/api/')) {
            Api::handle($path);
            return;
        }

        $user = Auth::currentUser();

        if ($path === '/') {
            $categories = Catalog::categories();
            $popular = array_slice(Catalog::getProducts(null, 'avtomaticheskie'), 0, 8);
            View::render('home', compact('categories', 'popular', 'user'));
            return;
        }
        if ($path === '/catalog') {
            View::render('catalog', ['categories' => Catalog::categories(), 'user' => $user]);
            return;
        }
        if (preg_match('#^/catalog/([^/]+)$#', $path, $m)) {
            $category = Catalog::getCategory($m[1]);
            if (!$category) {
                View::render('404', ['user' => $user]);
                return;
            }
            $items = Catalog::getProducts(null, $category['slug']);
            View::render('category', [
                'category' => $category,
                'items' => $items,
                'hasSubs' => count($category['subcategories']) > 0,
                'user' => $user,
            ]);
            return;
        }
        if (preg_match('#^/catalog/([^/]+)/([^/]+)$#', $path, $m)) {
            $category = Catalog::getCategory($m[1]);
            $subcategory = Catalog::getSubcategory($m[1], $m[2]);
            if (!$category || !$subcategory) {
                View::render('404', ['user' => $user]);
                return;
            }
            $items = Catalog::getProducts(null, $category['slug'], $subcategory['slug']);
            View::render('subcategory', compact('category', 'subcategory', 'items', 'user'));
            return;
        }
        if (preg_match('#^/product/([^/]+)$#', $path, $m)) {
            $product = Catalog::getBySlug($m[1]);
            if (!$product) {
                View::render('404', ['user' => $user]);
                return;
            }
            $category = Catalog::getCategory($product['category']);
            View::render('product', compact('product', 'category', 'user'));
            return;
        }
        if ($path === '/search') {
            $q = trim((string) ($_GET['q'] ?? ''));
            $results = $q ? Catalog::getProducts($q, null, null, 60) : [];
            View::render('search', compact('q', 'results', 'user'));
            return;
        }
        if ($path === '/cart') {
            View::render('cart', ['user' => $user]);
            return;
        }
        if ($path === '/checkout') {
            View::render('checkout', ['user' => $user]);
            return;
        }
        if ($path === '/login') {
            View::render('login', ['user' => $user]);
            return;
        }
        if ($path === '/register') {
            View::render('register', ['user' => $user]);
            return;
        }
        if ($path === '/account') {
            if (!$user) {
                Helpers::redirect('/login');
            }
            $orders = Orders::listByUser($user['id']);
            View::render('account', ['user' => $user, 'orders' => $orders]);
            return;
        }
        if ($path === '/delivery') {
            View::render('delivery', ['user' => $user]);
            return;
        }
        if ($path === '/payment') {
            View::render('payment', ['user' => $user]);
            return;
        }
        if ($path === '/contacts') {
            View::render('contacts', ['user' => $user]);
            return;
        }
        if (preg_match('#^/invoice/([^/]+)$#', $path, $m)) {
            $order = Orders::getById($m[1]);
            if (!$order || $order['type'] !== 'order') {
                View::render('404', ['user' => $user]);
                return;
            }
            echo Invoice::buildHtml($order, Config::siteUrl() . '/invoice/' . $order['id']);
            return;
        }
        if ($path === '/admin') {
            View::render('admin', [
                'user' => $user,
                'authed' => AdminAuth::check(),
                'adminPage' => true,
            ]);
            return;
        }
        if (preg_match('#^/admin/upd/([^/]+)$#', $path, $m)) {
            if (!AdminAuth::check()) {
                Helpers::redirect('/admin');
            }
            $order = Orders::getById($m[1]);
            if (!$order) {
                View::render('404', ['user' => $user]);
                return;
            }
            echo Upd::buildHtml($order);
            return;
        }

        http_response_code(404);
        View::render('404', ['user' => $user]);
    }
}
