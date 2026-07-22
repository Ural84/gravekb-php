<div class="container page-hero">
  <div class="breadcrumbs">
    <a href="/">Главная</a><span>/</span><a href="/catalog">Каталог</a><span>/</span>
    <a href="/catalog/<?= \App\Helpers::e($category['slug']) ?>"><?= \App\Helpers::e($category['name']) ?></a><span>/</span>
    <span><?= \App\Helpers::e($subcategory['name']) ?></span>
  </div>
  <h1 class="page-title"><?= \App\Helpers::e($subcategory['name']) ?></h1>
  <?php if (!empty($subcategory['priceFrom'])): ?>
    <p class="page-lead"><?= \App\Helpers::e(\App\Helpers::formatPriceFrom((float)$subcategory['priceFrom'])) ?></p>
  <?php endif; ?>
  <div class="product-grid" style="margin-top:1.25rem">
    <?php foreach ($items as $product): require __DIR__.'/../partials/product_card.php'; endforeach; ?>
  </div>
</div>
