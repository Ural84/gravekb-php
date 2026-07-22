<?php
/** @var string $content */
/** @var array $site */
/** @var array|null $user */
/** @var array $categories */
$pubUser = $user;
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= \App\Helpers::e($pageTitle ?? $site['name']) ?></title>
  <link rel="icon" href="/assets/img/favicon.svg" type="image/svg+xml" />
  <link rel="stylesheet" href="/assets/css/globals.css" />
</head>
<body>
<div class="site-shell">
  <header class="site-header">
    <div class="header-top">
      <div class="container header-top-inner">
        <span class="header-city"><?= \App\Helpers::e($site['address']) ?></span>
        <a href="<?= \App\Helpers::e($site['emailHref']) ?>" class="header-email"><?= \App\Helpers::e($site['email']) ?></a>
        <span class="header-hours"><?= \App\Helpers::e($site['workHours']) ?></span>
        <a href="<?= $pubUser ? '/account' : '/login' ?>" class="header-account-top" id="header-account-top">
          <?= $pubUser ? ('Кабинет · ' . \App\Helpers::e($pubUser['name'])) : 'Личный кабинет' ?>
        </a>
      </div>
    </div>
    <div class="container header-main">
      <a href="/" class="brand">
        <img src="/assets/img/logo.png" alt="<?= \App\Helpers::e($site['name']) ?>" class="brand-logo" width="694" height="179" />
      </a>
      <div class="header-search">
        <form action="/search" method="get" class="search-form">
          <input type="search" name="q" placeholder="Поиск по каталогу…" value="<?= \App\Helpers::e($_GET['q'] ?? '') ?>" />
          <button type="submit" class="btn btn-small">Найти</button>
        </form>
      </div>
      <div class="header-contacts">
        <a href="<?= \App\Helpers::e($site['phoneHref']) ?>" class="header-phone"><?= \App\Helpers::e($site['phone']) ?></a>
        <span class="header-callback">Заявка и консультация</span>
      </div>
      <button type="button" class="menu-toggle" aria-label="Меню" id="menu-toggle"><span></span><span></span><span></span></button>
    </div>
    <div class="header-nav-wrap" id="header-nav-wrap">
      <div class="container header-nav">
        <nav class="main-nav">
          <a href="/catalog">Каталог</a>
          <a href="/payment">Оплата</a>
          <a href="/delivery">Доставка</a>
          <a href="/contacts">Контакты</a>
          <a href="<?= $pubUser ? '/account' : '/login' ?>" id="nav-account"><?= $pubUser ? 'Кабинет' : 'Войти' ?></a>
        </nav>
        <a href="/cart" class="cart-link">
          <span class="cart-label">Корзина</span>
          <span class="cart-meta" id="cart-meta">пусто</span>
        </a>
      </div>
    </div>
  </header>
  <main class="site-main"><?= $content ?></main>
  <footer class="site-footer">
    <div class="container footer-grid">
      <div>
        <div class="footer-brand">
          <img src="/assets/img/logo2.png" alt="<?= \App\Helpers::e($site['name']) ?>" class="footer-logo" width="695" height="180" />
        </div>
        <p class="footer-text">Интернет-магазин оснасток для печатей и штампов. Подберём позицию под задачу и отправим заказ по России.</p>
      </div>
      <div>
        <div class="footer-title">Каталог</div>
        <ul class="footer-list">
          <?php foreach (array_slice($categories, 0, 6) as $c): ?>
            <li><a href="/catalog/<?= \App\Helpers::e($c['slug']) ?>"><?= \App\Helpers::e($c['name']) ?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <div>
        <div class="footer-title">Покупателям</div>
        <ul class="footer-list">
          <li><a href="/delivery">Доставка</a></li>
          <li><a href="/payment">Оплата</a></li>
          <li><a href="/contacts">Контакты</a></li>
          <li><a href="/cart">Корзина</a></li>
          <li><a href="/account">Личный кабинет</a></li>
        </ul>
      </div>
      <div>
        <div class="footer-title">Связь</div>
        <ul class="footer-list">
          <li><a href="<?= \App\Helpers::e($site['phoneHref']) ?>"><?= \App\Helpers::e($site['phone']) ?></a></li>
          <li><a href="<?= \App\Helpers::e($site['emailHref']) ?>"><?= \App\Helpers::e($site['email']) ?></a></li>
          <li><?= \App\Helpers::e($site['address']) ?></li>
          <li><?= \App\Helpers::e($site['workHours']) ?></li>
        </ul>
      </div>
    </div>
    <div class="container footer-bottom">
      <span>© <?= date('Y') ?> «<?= \App\Helpers::e($site['name']) ?>»</span>
      <span class="footer-credit">Crafted by Ural</span>
    </div>
  </footer>
</div>
<script src="/assets/js/app.js" defer></script>
<?php if (!empty($adminPage)): ?>
<script src="/assets/js/admin.js" defer></script>
<?php endif; ?>
</body>
</html>
