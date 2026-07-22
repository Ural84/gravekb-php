<div class="container page-hero">
  <div class="breadcrumbs">
    <a href="/">Главная</a>
    <span>/</span>
    <a href="/catalog">Каталог</a>
    <?php if (!empty($category)): ?>
      <span>/</span>
      <a href="/catalog/<?= \App\Helpers::e($category['slug']) ?>"><?= \App\Helpers::e($category['name']) ?></a>
    <?php endif; ?>
    <span>/</span>
    <span><?= \App\Helpers::e($product['name']) ?></span>
  </div>

  <div class="product-page">
    <div class="product-gallery">
      <?php if (!empty($product['image'])): ?>
        <img src="<?= \App\Helpers::e($product['image']) ?>" alt="<?= \App\Helpers::e($product['name']) ?>" />
      <?php else: ?>
        <div class="product-card-placeholder"><?= \App\Helpers::e(mb_substr($product['name'], 0, 1)) ?></div>
      <?php endif; ?>
    </div>
    <div class="panel product-info">
      <h1><?= \App\Helpers::e($product['name']) ?></h1>
      <p class="stock-badge">В наличии · без ограничения по количеству</p>
      <p class="product-price"><?= \App\Helpers::e(\App\Helpers::formatPrice($product['price'])) ?></p>
      <?php if (!empty($product['description'])): ?>
        <p class="product-desc"><?= \App\Helpers::e($product['description']) ?></p>
      <?php else: ?>
        <p class="product-desc">Можно заказать любое количество. Сроки отгрузки уточним при подтверждении заявки.</p>
      <?php endif; ?>
      <button
        type="button"
        class="btn btn-primary"
        data-add-to-cart
        data-id="<?= \App\Helpers::e($product['id']) ?>"
        data-slug="<?= \App\Helpers::e($product['slug']) ?>"
        data-name="<?= \App\Helpers::e($product['name']) ?>"
        data-price="<?= \App\Helpers::e((string) $product['price']) ?>"
        data-image="<?= \App\Helpers::e((string) ($product['image'] ?? '')) ?>"
      >В корзину</button>
      <p class="page-lead" style="margin-top: 1.25rem">
        После добавления в корзину оформите заявку — мы подтвердим заказ по телефону или почте.
      </p>
    </div>
  </div>
</div>
