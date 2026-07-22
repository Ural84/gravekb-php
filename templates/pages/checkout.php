<div class="container page-hero">
  <h1 class="page-title">Оформление заказа</h1>
  <p class="page-lead">Укажите контакты и реквизиты для счёта.</p>
  <form class="order-form panel" id="checkout-form" style="margin-top:1rem;max-width:640px">
    <label>Имя<input name="name" required value="<?= \App\Helpers::e($user['name'] ?? '') ?>" /></label>
    <label>Телефон<input name="phone" required value="<?= \App\Helpers::e($user['phone'] ?? '') ?>" /></label>
    <label>Email<input name="email" type="email" required value="<?= \App\Helpers::e($user['email'] ?? '') ?>" /></label>
    <label>Компания / ИП<input name="companyName" required value="<?= \App\Helpers::e($user['companyName'] ?? '') ?>" /></label>
    <label>ИНН<input name="inn" required value="<?= \App\Helpers::e($user['inn'] ?? '') ?>" /></label>
    <label>ОГРН / ОГРНИП<input name="ogrn" required value="<?= \App\Helpers::e($user['ogrn'] ?? '') ?>" /></label>
    <label>БИК<input name="bik" required value="<?= \App\Helpers::e($user['bik'] ?? '') ?>" /></label>
    <label>Расчётный счёт<input name="checkingAccount" required value="<?= \App\Helpers::e($user['checkingAccount'] ?? '') ?>" /></label>
    <label>Комментарий<textarea name="comment" rows="3"></textarea></label>
    <p class="form-error" id="checkout-error" hidden></p>
    <p class="form-success" id="checkout-success" hidden></p>
    <button class="btn btn-primary" type="submit">Отправить заявку</button>
  </form>
</div>
