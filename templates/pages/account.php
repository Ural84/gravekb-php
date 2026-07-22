<div class="container page-hero">
  <div class="section-head">
    <div>
      <h1 class="page-title">Личный кабинет</h1>
      <p class="page-lead"><?= \App\Helpers::e($user['email'] ?? '') ?></p>
    </div>
    <button type="button" class="btn btn-small" id="logout-btn">Выйти</button>
  </div>
  <form class="order-form panel" id="account-form" style="max-width:640px">
    <h2>Профиль и реквизиты</h2>
    <label>Имя<input name="name" required value="<?= \App\Helpers::e($user['name'] ?? '') ?>" /></label>
    <label>Телефон<input name="phone" required value="<?= \App\Helpers::e($user['phone'] ?? '') ?>" /></label>
    <label>Email<input value="<?= \App\Helpers::e($user['email'] ?? '') ?>" disabled /></label>
    <label>Компания / ИП<input name="companyName" required value="<?= \App\Helpers::e($user['companyName'] ?? '') ?>" /></label>
    <label>ИНН<input name="inn" required value="<?= \App\Helpers::e($user['inn'] ?? '') ?>" /></label>
    <label>ОГРН / ОГРНИП<input name="ogrn" required value="<?= \App\Helpers::e($user['ogrn'] ?? '') ?>" /></label>
    <label>БИК<input name="bik" required value="<?= \App\Helpers::e($user['bik'] ?? '') ?>" /></label>
    <label>Расчётный счёт<input name="checkingAccount" required value="<?= \App\Helpers::e($user['checkingAccount'] ?? '') ?>" /></label>
    <p class="form-error" id="account-error" hidden></p>
    <p class="form-success" id="account-success" hidden></p>
    <button class="btn btn-primary" type="submit">Сохранить</button>
  </form>
  <div class="panel" style="margin-top:1rem">
    <h2>Мои заказы</h2>
    <?php if (empty($orders)): ?>
      <p class="page-lead">Заказов пока нет.</p>
    <?php else: ?>
      <ul class="orders-list">
        <?php foreach ($orders as $o): ?>
          <li class="order-row">
            <strong><?= \App\Helpers::e($o['number']) ?></strong>
            · <?= \App\Helpers::e(\App\Helpers::formatPrice($o['total'])) ?>
            · <?= date('d.m.Y H:i', strtotime($o['createdAt'])) ?>
            <?php if ($o['type'] === 'order'): ?>
              · <a href="/invoice/<?= \App\Helpers::e($o['id']) ?>" target="_blank">Счёт</a>
            <?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>
</div>
