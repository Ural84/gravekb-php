<div class="container page-hero">
  <div class="breadcrumbs">
    <a href="/">Главная</a><span>/</span><a href="/catalog">Каталог</a>
    <?php if ($category): ?><span>/</span><a href="/catalog/<?= \App\Helpers::e($category['slug']) ?>"><?= \App\Helpers::e($category['name']) ?></a><?php endif; ?>
    <span>/</span><span><?= \App\Helpers::e($product['name']) ?></span>
  </div>
  <div class="product-page">
    <div class="product-media">
      <?php if (!empty($product['image'])): ?>
        <img src="<?= \App\Helpers::e($product['image']) ?>" alt="<?= \App\Helpers::e($product['name']) ?>" />
      <?php else: ?>
        <div class="product-card-placeholder" style="min-height:280px"></div>
      <?php endif; ?>
    </div>
    <div class="product-info">
      <h1 class="page-title"><?= \App\Helpers::e($product['name']) ?></h1>
      <p class="product-price"><?= \App\Helpers::e(\App\Helpers::formatPrice($product['price'])) ?></p>
      <p class="page-lead"><?= nl2br(\App\Helpers::e($product['description'])) ?></p>
      <button type="button" class="btn btn-primary" data-add-to-cart
        data-id="<?= \App\Helpers::e($product['id']) ?>"
        data-slug="<?= \App\Helpers::e($product['slug']) ?>"
        data-name="<?= \App\Helpers::e($product['name']) ?>"
        data-price="<?= \App\Helpers::e((string)$product['price']) ?>"
        data-image="<?= \App\Helpers::e((string)($product['image'] ?? '')) ?>">В корзину</button>
    </div>
  </div>
</div>
