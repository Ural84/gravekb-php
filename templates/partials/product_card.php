<?php /** @var array $product */ ?>
<article class="product-card">
  <a href="/product/<?= \App\Helpers::e($product['slug']) ?>" class="product-card-media">
    <?php if (!empty($product['image'])): ?>
      <img src="<?= \App\Helpers::e($product['image']) ?>" alt="<?= \App\Helpers::e($product['name']) ?>" loading="lazy" />
    <?php else: ?>
      <div class="product-card-placeholder"><?= \App\Helpers::e(mb_substr($product['name'], 0, 1)) ?></div>
    <?php endif; ?>
  </a>
  <div class="product-card-body">
    <a href="/product/<?= \App\Helpers::e($product['slug']) ?>" class="product-card-title"><?= \App\Helpers::e($product['name']) ?></a>
    <?php if (!empty($product['description'])): ?>
      <p class="product-card-desc"><?= \App\Helpers::e($product['description']) ?></p>
    <?php endif; ?>
    <div class="product-card-footer">
      <span class="product-card-price"><?= \App\Helpers::e(\App\Helpers::formatPrice($product['price'])) ?></span>
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
    </div>
  </div>
</article>
