<section class="container">
  <div class="hero">
    <div class="hero-inner">
      <div class="hero-brand">
        <img src="/assets/img/logo2.png" alt="<?= \App\Helpers::e($site['name']) ?>" class="hero-logo" width="695" height="180" />
      </div>
      <h1 class="hero-title">Оснастки для печатей и штампов с доставкой по России</h1>
      <p class="hero-text">Каталог автоматических и ручных оснасток, датеров, краски и расходников. Оформите заявку на сайте — подтвердим наличие и сроки.</p>
      <div class="hero-actions">
        <a href="/catalog" class="btn btn-primary">Открыть каталог</a>
        <a href="<?= \App\Helpers::e($site['phoneHref']) ?>" class="btn btn-ghost"><?= \App\Helpers::e($site['phone']) ?></a>
      </div>
    </div>
  </div>
</section>
<section class="container section">
  <div class="benefits">
    <article class="benefit"><h3>Быстрый заказ</h3><p>Через сайт, почту или телефон. Заявка уходит менеджеру сразу.</p></article>
    <article class="benefit"><h3>Короткие сроки</h3><p>Подготовка и отправка заказа обычно в течение 1–2 рабочих дней.</p></article>
    <article class="benefit"><h3>Подбор под задачу</h3><p>Поможем выбрать оснастку, подушку и краску под нужный оттиск.</p></article>
  </div>
</section>
<section class="container section">
  <div class="section-head">
    <div><h2>Категории</h2><p>Весь ассортимент оснасток и расходных материалов в одном каталоге.</p></div>
    <a href="/catalog">Смотреть всё →</a>
  </div>
  <div class="category-grid">
    <?php foreach ($categories as $c): ?>
      <?php $href = '/catalog/' . $c['slug']; $name = $c['name']; $priceFrom = $c['priceFrom']; require __DIR__ . '/../partials/category_card.php'; ?>
    <?php endforeach; ?>
  </div>
</section>
<section class="container section">
  <div class="section-head">
    <div><h2>Популярные автоматические</h2><p>Часто заказывают для офиса и производства клише.</p></div>
    <a href="/catalog/avtomaticheskie">В категорию →</a>
  </div>
  <div class="product-grid">
    <?php foreach ($popular as $product): require __DIR__ . '/../partials/product_card.php'; endforeach; ?>
  </div>
</section>
