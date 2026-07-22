<div class="container page-hero">
  <div class="breadcrumbs"><a href="/">Главная</a><span>/</span><span>Поиск</span></div>
  <h1 class="page-title">Поиск по каталогу</h1>
  <div style="max-width:560px;margin-top:1rem">
    <form action="/search" method="get" class="search-form">
      <input type="search" name="q" value="<?= \App\Helpers::e($q) ?>" placeholder="Поиск…" />
      <button class="btn btn-small" type="submit">Найти</button>
    </form>
  </div>
  <?php if ($q): ?>
    <p class="page-lead" style="margin-top:1.25rem">По запросу «<?= \App\Helpers::e($q) ?>» найдено: <?= count($results) ?></p>
    <div class="product-grid" style="margin-top:1.25rem">
      <?php foreach ($results as $product): require __DIR__.'/../partials/product_card.php'; endforeach; ?>
    </div>
    <?php if (!$results): ?><div class="panel empty-state" style="margin-top:1rem"><p>Ничего не найдено.</p></div><?php endif; ?>
  <?php endif; ?>
</div>
