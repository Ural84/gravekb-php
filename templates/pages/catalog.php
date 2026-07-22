<div class="container page-hero">
  <div class="breadcrumbs"><a href="/">Главная</a><span>/</span><span>Каталог</span></div>
  <h1 class="page-title">Каталог</h1>
  <p class="page-lead">Выберите раздел оснасток и расходных материалов.</p>
  <div class="category-grid" style="margin-top:1.25rem">
    <?php foreach ($categories as $c): ?>
      <?php $href='/catalog/'.$c['slug']; $name=$c['name']; $priceFrom=$c['priceFrom']; require __DIR__.'/../partials/category_card.php'; ?>
    <?php endforeach; ?>
  </div>
</div>
