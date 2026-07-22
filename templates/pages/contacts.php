<div class="container page-hero">
  <h1 class="page-title">Контакты</h1>
  <div class="panel" style="margin-top:1rem">
    <p><a href="<?= \App\Helpers::e($site['phoneHref']) ?>"><?= \App\Helpers::e($site['phone']) ?></a></p>
    <p><a href="<?= \App\Helpers::e($site['emailHref']) ?>"><?= \App\Helpers::e($site['email']) ?></a></p>
    <p><?= \App\Helpers::e($site['address']) ?></p>
    <p><?= \App\Helpers::e($site['workHours']) ?></p>
  </div>
  <form class="order-form panel" id="contact-form" style="margin-top:1rem;max-width:560px">
    <h2>Написать нам</h2>
    <label>Имя<input name="name" required /></label>
    <label>Телефон<input name="phone" required /></label>
    <label>Email<input type="email" name="email" /></label>
    <label>Тема
      <select name="topic">
        <option value="sales">Вопрос по товару</option>
        <option value="manager">Связаться с менеджером</option>
      </select>
    </label>
    <label>Сообщение<textarea name="comment" rows="4" required></textarea></label>
    <p class="form-error" id="contact-error" hidden></p>
    <p class="form-success" id="contact-success" hidden></p>
    <button class="btn btn-primary" type="submit">Отправить</button>
  </form>
</div>
