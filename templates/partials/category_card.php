<?php /** @var string $href */ /** @var string $name */ /** @var float|int|null $priceFrom */ ?>
<a href="<?= \App\Helpers::e($href) ?>" class="category-card">
  <h3><?= \App\Helpers::e($name) ?></h3>
  <?php if ($priceFrom !== null && $priceFrom !== ''): ?>
    <p><?= \App\Helpers::e(\App\Helpers::formatPriceFrom((float) $priceFrom)) ?></p>
  <?php endif; ?>
</a>
