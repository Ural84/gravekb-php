<div class="container page-hero">
  <div class="breadcrumbs">
    <a href="/">Главная</a>
    <span>/</span>
    <a href="/cart">Корзина</a>
    <span>/</span>
    <span>Оформление</span>
  </div>
  <h1 class="page-title">Оформление заказа</h1>
  <p class="page-lead">Укажите контакты и реквизиты для счёта.</p>
  <div class="checkout-layout" style="margin-top:1.25rem;display:grid;gap:1rem;grid-template-columns:minmax(0,1.2fr) minmax(260px,.8fr);align-items:start">
    <form class="order-form panel" id="checkout-form">
      <h2 class="form-section-title">Контакты</h2>
      <label>Имя<input name="name" required value="<?= \App\Helpers::e($user['name'] ?? '') ?>" /></label>
      <label>Телефон<input name="phone" required value="<?= \App\Helpers::e($user['phone'] ?? '') ?>" /></label>
      <label>Email<input name="email" type="email" required value="<?= \App\Helpers::e($user['email'] ?? '') ?>" /></label>
      <h2 class="form-section-title">Реквизиты для счёта</h2>
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
    <aside class="panel" id="checkout-summary">
      <h2 style="margin-top:0">Состав заказа</h2>
      <p class="page-lead">Состав подтянется из корзины.</p>
    </aside>
  </div>
</div>
<script>
document.addEventListener("DOMContentLoaded", () => {
  try {
    const items = JSON.parse(localStorage.getItem("gravekb-cart") || "[]");
    const box = document.getElementById("checkout-summary");
    if (!box) return;
    if (!items.length) {
      box.innerHTML = '<h2 style="margin-top:0">Корзина пуста</h2><p class="page-lead"><a href="/catalog">Перейти в каталог</a></p>';
      return;
    }
    const money = (n) => new Intl.NumberFormat("ru-RU").format(n) + " ₽";
    const total = items.reduce((s, i) => s + i.price * i.qty, 0);
    box.innerHTML = `<h2 style="margin-top:0">Состав заказа</h2>
      <ul style="padding-left:1.1rem;margin:0 0 1rem">${items.map((i) => `<li>${i.name} × ${i.qty} — ${money(i.price * i.qty)}</li>`).join("")}</ul>
      <p style="font-size:1.25rem;font-weight:800">Итого: ${money(total)}</p>`;
  } catch (e) {}
});
</script>
<style>
@media (max-width: 900px) {
  .checkout-layout { grid-template-columns: 1fr !important; }
}
</style>
