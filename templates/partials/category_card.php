<?php /** @var string $href */ /** @var string $name */ /** @var float|int|null $priceFrom */ ?>
<a href="<?= \App\Helpers::e($href) ?>" class="category-card">
  <span class="category-card-name"><?= \App\Helpers::e($name) ?></span>
  <?php if (is_numeric($priceFrom)): ?>
    <span class="category-card-price"><?= \App\Helpers::e(\App\Helpers::formatPriceFrom((float) $priceFrom)) ?></span>
  <?php endif; ?>
</a>
