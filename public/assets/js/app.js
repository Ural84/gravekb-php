(function () {
  const KEY = "gravekb-cart";

  function money(n) {
    return new Intl.NumberFormat("ru-RU").format(Number(n) || 0) + " ₽";
  }

  function readCart() {
    try {
      return JSON.parse(localStorage.getItem(KEY) || "[]");
    } catch {
      return [];
    }
  }

  function writeCart(items) {
    localStorage.setItem(KEY, JSON.stringify(items));
    updateBadge();
  }

  function updateBadge() {
    const items = readCart();
    const count = items.reduce((s, i) => s + (i.qty || 0), 0);
    const total = items.reduce((s, i) => s + i.price * i.qty, 0);
    const el = document.getElementById("cart-meta");
    if (el) el.textContent = count ? count + " шт · " + money(total) : "пусто";
  }

  function addItem(item) {
    const items = readCart();
    const idx = items.findIndex((x) => x.id === item.id);
    if (idx >= 0) items[idx].qty += 1;
    else items.push({ ...item, qty: 1 });
    writeCart(items);
  }

  document.getElementById("menu-toggle")?.addEventListener("click", () => {
    document.getElementById("header-nav-wrap")?.classList.toggle("is-open");
  });

  document.querySelectorAll("[data-add-to-cart]").forEach((btn) => {
    btn.addEventListener("click", () => {
      addItem({
        id: btn.dataset.id,
        slug: btn.dataset.slug,
        name: btn.dataset.name,
        price: Number(btn.dataset.price) || 0,
        image: btn.dataset.image || null,
      });
      btn.textContent = "Добавлено";
      setTimeout(() => (btn.textContent = "В корзину"), 900);
    });
  });

  const cartPage = document.getElementById("cart-page");
  if (cartPage) {
    function renderCart() {
      const items = readCart();
      if (!items.length) {
        cartPage.innerHTML =
          '<p class="page-lead">Корзина пуста.</p><a class="btn btn-primary" href="/catalog">В каталог</a>';
        return;
      }
      const total = items.reduce((s, i) => s + i.price * i.qty, 0);
      cartPage.innerHTML =
        "<ul>" +
        items
          .map(
            (i, idx) =>
              `<li style="display:flex;gap:.75rem;align-items:center;margin:.5rem 0;flex-wrap:wrap">
                <strong style="flex:1">${i.name}</strong>
                <input type="number" min="1" value="${i.qty}" data-qty="${idx}" style="width:70px" />
                <span>${money(i.price * i.qty)}</span>
                <button type="button" class="btn btn-small" data-remove="${idx}">Удалить</button>
              </li>`
          )
          .join("") +
        `</ul><p><strong>Итого: ${money(total)}</strong></p>
         <a class="btn btn-primary" href="/checkout">Оформить заказ</a>`;
      cartPage.querySelectorAll("[data-qty]").forEach((input) => {
        input.addEventListener("change", () => {
          const items = readCart();
          const i = Number(input.dataset.qty);
          items[i].qty = Math.max(1, Number(input.value) || 1);
          writeCart(items);
          renderCart();
        });
      });
      cartPage.querySelectorAll("[data-remove]").forEach((btn) => {
        btn.addEventListener("click", () => {
          const items = readCart();
          items.splice(Number(btn.dataset.remove), 1);
          writeCart(items);
          renderCart();
        });
      });
    }
    renderCart();
  }

  async function postJson(url, body) {
    const res = await fetch(url, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(body),
    });
    const data = await res.json().catch(() => ({}));
    return { res, data };
  }

  const checkout = document.getElementById("checkout-form");
  if (checkout) {
    checkout.addEventListener("submit", async (e) => {
      e.preventDefault();
      const err = document.getElementById("checkout-error");
      const ok = document.getElementById("checkout-success");
      err.hidden = true;
      ok.hidden = true;
      const fd = new FormData(checkout);
      const items = readCart();
      if (!items.length) {
        err.textContent = "Корзина пуста";
        err.hidden = false;
        return;
      }
      const body = Object.fromEntries(fd.entries());
      body.items = items;
      body.type = "order";
      const { res, data } = await postJson("/api/order", body);
      if (!res.ok) {
        err.textContent = data.error || "Ошибка";
        err.hidden = false;
        return;
      }
      writeCart([]);
      ok.innerHTML = `Заявка ${data.order.number} принята. <a href="/invoice/${data.order.id}" target="_blank">Открыть счёт</a>`;
      ok.hidden = false;
      checkout.reset();
    });
  }

  const contact = document.getElementById("contact-form");
  if (contact) {
    contact.addEventListener("submit", async (e) => {
      e.preventDefault();
      const err = document.getElementById("contact-error");
      const ok = document.getElementById("contact-success");
      err.hidden = true;
      ok.hidden = true;
      const body = Object.fromEntries(new FormData(contact).entries());
      body.type = "contact";
      body.items = [];
      const { res, data } = await postJson("/api/order", body);
      if (!res.ok) {
        err.textContent = data.error || "Ошибка";
        err.hidden = false;
        return;
      }
      ok.textContent = "Сообщение отправлено";
      ok.hidden = false;
      contact.reset();
    });
  }

  async function loadCaptcha() {
    const res = await fetch("/api/auth/captcha");
    const data = await res.json();
    const q = document.getElementById("captcha-q");
    const t = document.getElementById("captcha-token");
    if (q) q.textContent = data.question;
    if (t) t.value = data.token;
  }
  if (document.getElementById("register-form")) loadCaptcha();

  document.getElementById("login-form")?.addEventListener("submit", async (e) => {
    e.preventDefault();
    const err = document.getElementById("login-error");
    err.hidden = true;
    const body = Object.fromEntries(new FormData(e.target).entries());
    const { res, data } = await postJson("/api/auth/login", body);
    if (!res.ok) {
      err.textContent = data.error || "Ошибка";
      err.hidden = false;
      return;
    }
    location.href = "/account";
  });

  document.getElementById("register-form")?.addEventListener("submit", async (e) => {
    e.preventDefault();
    const err = document.getElementById("register-error");
    err.hidden = true;
    const body = Object.fromEntries(new FormData(e.target).entries());
    const { res, data } = await postJson("/api/auth/register", body);
    if (!res.ok) {
      err.textContent = data.error || "Ошибка";
      err.hidden = false;
      loadCaptcha();
      return;
    }
    location.href = "/account";
  });

  document.getElementById("account-form")?.addEventListener("submit", async (e) => {
    e.preventDefault();
    const err = document.getElementById("account-error");
    const ok = document.getElementById("account-success");
    err.hidden = true;
    ok.hidden = true;
    const body = Object.fromEntries(new FormData(e.target).entries());
    const res = await fetch("/api/account", {
      method: "PATCH",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(body),
    });
    const data = await res.json().catch(() => ({}));
    if (!res.ok) {
      err.textContent = data.error || "Ошибка";
      err.hidden = false;
      return;
    }
    ok.textContent = "Сохранено";
    ok.hidden = false;
  });

  document.getElementById("logout-btn")?.addEventListener("click", async () => {
    await postJson("/api/auth/logout", {});
    location.href = "/";
  });

  updateBadge();
})();
