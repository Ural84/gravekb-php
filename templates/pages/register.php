<div class="container page-hero">
  <div class="breadcrumbs"><a href="/">Главная</a><span>/</span><span>Регистрация</span></div>
  <h1 class="page-title">Регистрация</h1>
  <div class="auth-card auth-card-wide panel">
    <form class="order-form" id="register-form">
      <h2 class="form-section-title">Контакты</h2>
      <label>Имя<input name="name" required /></label>
      <label>Телефон<input name="phone" required /></label>
      <label>Email<input type="email" name="email" required /></label>
      <label>Пароль<input type="password" name="password" minlength="6" required /></label>
      <h2 class="form-section-title">Реквизиты</h2>
      <label>Компания / ИП<input name="companyName" required /></label>
      <label>ИНН<input name="inn" required /></label>
      <label>ОГРН / ОГРНИП<input name="ogrn" required /></label>
      <label>БИК<input name="bik" required /></label>
      <label>Расчётный счёт<input name="checkingAccount" required /></label>
      <label>Сколько будет? <span id="captcha-q">…</span>
        <input name="captchaAnswer" required inputmode="numeric" />
      </label>
      <input type="hidden" name="captchaToken" id="captcha-token" />
      <p class="form-error" id="register-error" hidden></p>
      <button class="btn btn-primary" type="submit">Создать аккаунт</button>
    </form>
    <p class="page-lead" style="margin-top:1rem">Уже есть аккаунт? <a href="/login">Войти</a></p>
  </div>
</div>
