<div class="container page-hero">
  <div class="breadcrumbs">
    <a href="/">Главная</a><span>/</span><a href="/catalog">Каталог</a><span>/</span><span><?= \App\Helpers::e($category['name']) ?></span>
  </div>
  <h1 class="page-title"><?= \App\Helpers::e($category['name']) ?></h1>
  <?php if (!empty($hasSubs)): ?>
    <div class="category-grid" style="margin-top:1.25rem">
      <?php foreach ($category['subcategories'] as $s): ?>
        <?php $href='/catalog/'.$category['slug'].'/'.$s['slug']; $name=$s['name']; $priceFrom=$s['priceFrom']??null; require __DIR__.'/../partials/category_card.php'; ?>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
  <div class="product-grid" style="margin-top:1.5rem">
    <?php foreach ($items as $product): require __DIR__.'/../partials/product_card.php'; endforeach; ?>
  </div>
  <?php if (!$items): ?><div class="panel empty-state"><p>В этом разделе пока нет товаров.</p></div><?php endif; ?>
</div>
