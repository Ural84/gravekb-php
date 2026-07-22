<?php $adminPage = true; ?>
<div class="container page-hero" id="admin-root" data-authed="<?= !empty($authed) ? '1' : '0' ?>">
  <div id="admin-login" <?= !empty($authed) ? 'hidden' : '' ?>>
    <h1 class="page-title">Панель хозяина</h1>
    <div class="auth-card panel">
      <form class="order-form" id="admin-login-form">
        <label>Логин<input name="login" required autocomplete="username" /></label>
        <label>Пароль<input type="password" name="password" required autocomplete="current-password" /></label>
        <p class="form-error" id="admin-login-error" hidden></p>
        <button class="btn btn-primary" type="submit">Войти</button>
      </form>
    </div>
  </div>
  <div id="admin-app" <?= empty($authed) ? 'hidden' : '' ?>></div>
</div>
