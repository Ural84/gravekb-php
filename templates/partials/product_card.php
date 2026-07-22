<?php /** @var array $product */ ?>
<article class="product-card">
  <a href="/product/<?= \App\Helpers::e($product['slug']) ?>" class="product-card-link">
    <?php if (!empty($product['image'])): ?>
      <img src="<?= \App\Helpers::e($product['image']) ?>" alt="" class="product-card-image" loading="lazy" />
    <?php else: ?>
      <div class="product-card-image product-card-placeholder"></div>
    <?php endif; ?>
    <h3 class="product-card-title"><?= \App\Helpers::e($product['name']) ?></h3>
    <div class="product-card-price"><?= \App\Helpers::e(\App\Helpers::formatPrice($product['price'])) ?></div>
  </a>
  <button
    type="button"
    class="btn btn-small"
    data-add-to-cart
    data-id="<?= \App\Helpers::e($product['id']) ?>"
    data-slug="<?= \App\Helpers::e($product['slug']) ?>"
    data-name="<?= \App\Helpers::e($product['name']) ?>"
    data-price="<?= \App\Helpers::e((string) $product['price']) ?>"
    data-image="<?= \App\Helpers::e((string) ($product['image'] ?? '')) ?>"
  >В корзину</button>
</article>
