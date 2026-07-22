(function () {
  const root = document.getElementById("admin-root");
  if (!root) return;

  const money = (n) =>
    new Intl.NumberFormat("ru-RU").format(Number(n) || 0) + " ₽";
  const statusLabel = {
    new: "Новая",
    processing: "В работе",
    done: "Выполнена",
    cancelled: "Отменена",
  };

  async function api(url, opts = {}) {
    const res = await fetch(url, {
      cache: "no-store",
      headers: { "Content-Type": "application/json", ...(opts.headers || {}) },
      ...opts,
    });
    const data = await res.json().catch(() => ({}));
    return { res, data };
  }

  document.getElementById("admin-login-form")?.addEventListener("submit", async (e) => {
    e.preventDefault();
    const err = document.getElementById("admin-login-error");
    err.hidden = true;
    const body = Object.fromEntries(new FormData(e.target).entries());
    const { res, data } = await api("/api/admin/login", {
      method: "POST",
      body: JSON.stringify(body),
    });
    if (!res.ok) {
      err.textContent = data.error || "Ошибка";
      err.hidden = false;
      return;
    }
    location.reload();
  });

  if (root.dataset.authed !== "1") return;

  const app = document.getElementById("admin-app");
  let tab = "orders";
  let orders = [];
  let selectedClientKey = null;
  let clientQuery = "";

  function clientKey(o) {
    const email = (o.email || "").trim().toLowerCase();
    if (email) return "email:" + email;
    const phone = (o.phone || "").replace(/\D/g, "");
    if (phone) return "phone:" + phone;
    return "name:" + ((o.name || "").trim().toLowerCase() || "unknown");
  }

  function buildClients(list) {
    const map = new Map();
    for (const order of list) {
      const key = clientKey(order);
      const total = order.type === "order" ? order.total : 0;
      const ex = map.get(key);
      if (!ex) {
        map.set(key, {
          key,
          name: order.name || "Без имени",
          phone: order.phone || "",
          email: order.email || "",
          companyName: order.companyName || "",
          orderCount: 1,
          total,
          lastAt: order.createdAt,
        });
      } else {
        ex.orderCount++;
        ex.total += total;
        if (new Date(order.createdAt) > new Date(ex.lastAt)) {
          ex.lastAt = order.createdAt;
          ex.name = order.name || ex.name;
          ex.phone = order.phone || ex.phone;
          ex.email = order.email || ex.email;
          ex.companyName = order.companyName || ex.companyName;
        }
      }
    }
    return [...map.values()].sort(
      (a, b) => new Date(b.lastAt) - new Date(a.lastAt)
    );
  }

  async function loadOrders() {
    const { res, data } = await api("/api/admin/orders");
    if (!res.ok) {
      location.reload();
      return;
    }
    orders = data.orders || [];
    render();
  }

  async function logout() {
    await api("/api/admin/login", {
      method: "POST",
      body: JSON.stringify({ action: "logout" }),
    });
    location.reload();
  }

  function render() {
    const clients = buildClients(orders);
    const q = clientQuery.trim().toLowerCase();
    const filtered = !q
      ? clients
      : clients.filter((c) =>
          `${c.name} ${c.companyName} ${c.email} ${c.phone}`
            .toLowerCase()
            .includes(q)
        );
    const visible = selectedClientKey
      ? orders.filter((o) => clientKey(o) === selectedClientKey)
      : orders;

    if (tab === "orders") {
      app.innerHTML = `
        <div class="section-head">
          <div><h1 class="page-title">Панель хозяина</h1>
          <p class="page-lead">Всего: ${orders.length} · клиентов: ${clients.length}</p></div>
          <div style="display:flex;gap:.6rem;flex-wrap:wrap">
            <button type="button" class="btn btn-small" id="adm-refresh">Обновить</button>
            <button type="button" class="btn btn-small" id="adm-logout">Выйти</button>
          </div>
        </div>
        <div class="admin-tabs">
          <button type="button" class="btn btn-small is-done" data-tab="orders">Заявки</button>
          <button type="button" class="btn btn-small" data-tab="users">Клиенты</button>
          <button type="button" class="btn btn-small" data-tab="products">Товары</button>
        </div>
        <div class="admin-layout">
          <aside class="panel admin-clients">
            <div class="admin-clients-head"><h2>Клиенты</h2>
              ${selectedClientKey ? '<button type="button" class="btn btn-small" id="adm-all">Все заказы</button>' : ""}
            </div>
            <input class="admin-client-search" id="adm-client-q" type="search" placeholder="Поиск…" value="${clientQuery.replace(/"/g, "&quot;")}" />
            <ul class="admin-client-list">
              ${filtered
                .map(
                  (c) => `<li><button type="button" class="admin-client-item ${
                    selectedClientKey === c.key ? "is-active" : ""
                  }" data-client="${c.key}">
                    <span class="admin-client-name">${c.companyName || c.name}</span>
                    <span class="admin-client-meta">${[c.phone, c.email].filter(Boolean).join(" · ")}</span>
                    <span class="admin-client-stats">${c.orderCount} заказ(ов)${c.total ? " · " + money(c.total) : ""}</span>
                  </button></li>`
                )
                .join("")}
            </ul>
          </aside>
          <section class="admin-orders">
            <div class="orders-list panel">
              ${visible
                .map(
                  (o) => `<article class="order-row" data-oid="${o.id}">
                    <div class="order-row-top"><strong>${o.number}</strong>
                      <span class="order-status">${statusLabel[o.status] || o.status}</span></div>
                    <div class="order-row-meta">${new Date(o.createdAt).toLocaleString("ru-RU")} · ${
                      o.type === "order" ? money(o.total) : "сообщение"
                    }</div>
                    <p><strong>${o.name}</strong> · <a href="tel:${o.phone}">${o.phone}</a>${
                      o.email ? ` · <a href="mailto:${o.email}">${o.email}</a>` : ""
                    }</p>
                    ${
                      o.type === "order"
                        ? `<p><a href="/invoice/${o.id}" target="_blank">Счёт</a>
                           · <a href="/admin/upd/${o.id}" target="_blank">УПД</a></p>`
                        : ""
                    }
                    ${o.comment ? `<p class="page-lead">${o.comment}</p>` : ""}
                    ${
                      o.items?.length
                        ? `<ul>${o.items
                            .map(
                              (i) =>
                                `<li>${i.name} × ${i.qty} — ${money(i.price * i.qty)}</li>`
                            )
                            .join("")}</ul>`
                        : ""
                    }
                    <button type="button" class="btn btn-small" data-edit="${o.id}">Редактировать</button>
                    <div class="admin-status-actions" data-status-for="${o.id}">
                      ${["new", "processing", "done", "cancelled"]
                        .map(
                          (s) =>
                            `<button type="button" class="btn btn-small ${
                              o.status === s ? "is-done" : ""
                            }" data-status="${s}">${statusLabel[s]}</button>`
                        )
                        .join("")}
                      <button type="button" class="btn btn-small admin-delete-btn" data-del="${o.id}">Удалить</button>
                    </div>
                    <div class="admin-order-editor panel" id="edit-${o.id}" hidden></div>
                  </article>`
                )
                .join("") || '<div class="panel empty-state"><p>Заявок пока нет.</p></div>'}
            </div>
          </section>
        </div>`;
    } else if (tab === "users") {
      app.innerHTML = `
        <div class="section-head"><div><h1 class="page-title">Клиенты</h1></div>
          <button type="button" class="btn btn-small" id="adm-logout">Выйти</button></div>
        <div class="admin-tabs">
          <button type="button" class="btn btn-small" data-tab="orders">Заявки</button>
          <button type="button" class="btn btn-small is-done" data-tab="users">Клиенты</button>
          <button type="button" class="btn btn-small" data-tab="products">Товары</button>
        </div>
        <div id="users-panel" class="panel"><p class="page-lead">Загрузка…</p></div>`;
      loadUsers();
    } else {
      app.innerHTML = `
        <div class="section-head"><div><h1 class="page-title">Товары</h1></div>
          <button type="button" class="btn btn-small" id="adm-logout">Выйти</button></div>
        <div class="admin-tabs">
          <button type="button" class="btn btn-small" data-tab="orders">Заявки</button>
          <button type="button" class="btn btn-small" data-tab="users">Клиенты</button>
          <button type="button" class="btn btn-small is-done" data-tab="products">Товары</button>
        </div>
        <div id="products-panel" class="admin-edit-layout"></div>`;
      loadProducts();
    }

    bindCommon();
  }

  function bindCommon() {
    app.querySelectorAll("[data-tab]").forEach((b) =>
      b.addEventListener("click", () => {
        tab = b.dataset.tab;
        if (tab === "orders") loadOrders();
        else render();
      })
    );
    document.getElementById("adm-logout")?.addEventListener("click", logout);
    document.getElementById("adm-refresh")?.addEventListener("click", loadOrders);
    document.getElementById("adm-all")?.addEventListener("click", () => {
      selectedClientKey = null;
      render();
    });
    document.getElementById("adm-client-q")?.addEventListener("input", (e) => {
      clientQuery = e.target.value;
      render();
    });
    app.querySelectorAll("[data-client]").forEach((b) =>
      b.addEventListener("click", () => {
        selectedClientKey = b.dataset.client;
        render();
      })
    );
    app.querySelectorAll("[data-status]").forEach((b) =>
      b.addEventListener("click", async () => {
        const id = b.closest("[data-status-for]")?.dataset.statusFor;
        await api("/api/admin/orders", {
          method: "PATCH",
          body: JSON.stringify({ id, status: b.dataset.status }),
        });
        loadOrders();
      })
    );
    app.querySelectorAll("[data-del]").forEach((b) =>
      b.addEventListener("click", async () => {
        if (!confirm("Удалить заявку?")) return;
        await api("/api/admin/orders", {
          method: "DELETE",
          body: JSON.stringify({ id: b.dataset.del }),
        });
        loadOrders();
      })
    );
    app.querySelectorAll("[data-edit]").forEach((b) =>
      b.addEventListener("click", () => openEditor(b.dataset.edit))
    );
  }

  function openEditor(id) {
    const order = orders.find((o) => o.id === id);
    const box = document.getElementById("edit-" + id);
    if (!order || !box) return;
    box.hidden = false;
    box.innerHTML = `
      <h3>Редактирование ${order.number}</h3>
      <div class="admin-editor-grid">
        <label>Имя<input id="e-name" value="${order.name || ""}" /></label>
        <label>Компания<input id="e-company" value="${order.companyName || ""}" /></label>
        <label>Телефон<input id="e-phone" value="${order.phone || ""}" /></label>
        <label>Email<input id="e-email" value="${order.email || ""}" /></label>
      </div>
      <label>Комментарий<textarea id="e-comment" rows="2">${order.comment || ""}</textarea></label>
      <div class="admin-items-editor" id="e-items">
        ${(order.items || [])
          .map(
            (it, i) => `<div class="admin-item-row">
            <input data-f="name" data-i="${i}" value="${(it.name || "").replace(/"/g, "&quot;")}" />
            <input type="number" data-f="qty" data-i="${i}" value="${it.qty}" />
            <input type="number" data-f="price" data-i="${i}" value="${it.price}" />
          </div>`
          )
          .join("")}
      </div>
      <button type="button" class="btn btn-primary" id="e-save">Сохранить</button>`;
    let items = (order.items || []).map((x) => ({ ...x }));
    box.querySelectorAll("[data-f]").forEach((input) => {
      input.addEventListener("change", () => {
        const i = Number(input.dataset.i);
        const f = input.dataset.f;
        items[i][f] = f === "name" ? input.value : Number(input.value) || 0;
      });
    });
    document.getElementById("e-save").onclick = async () => {
      await api("/api/admin/orders", {
        method: "PATCH",
        body: JSON.stringify({
          id,
          name: document.getElementById("e-name").value,
          companyName: document.getElementById("e-company").value,
          phone: document.getElementById("e-phone").value,
          email: document.getElementById("e-email").value,
          comment: document.getElementById("e-comment").value,
          items,
        }),
      });
      loadOrders();
    };
  }

  async function loadUsers() {
    const panel = document.getElementById("users-panel");
    const { data } = await api("/api/admin/users");
    const users = data.users || [];
    panel.innerHTML = `
      <p class="page-lead">Всего аккаунтов: ${users.length}</p>
      <ul class="admin-product-list">
        ${users
          .map(
            (u) => `<li>
            <div class="admin-product-row">
              <div>
                <div class="admin-client-name">${u.companyName || u.name}</div>
                <div class="admin-client-meta">${u.email} · ${u.phone || ""}</div>
                <div class="admin-client-meta">ИНН ${u.inn || "—"} · ОГРН ${u.ogrn || "—"} · БИК ${u.bik || "—"} · р/с ${u.checkingAccount || "—"}</div>
                <div class="admin-client-meta">Регистрация: ${new Date(u.createdAt).toLocaleString("ru-RU")} · заказов: ${u.orderCount}</div>
              </div>
              <button type="button" class="btn btn-small admin-delete-btn" data-udel="${u.id}">Удалить</button>
            </div></li>`
          )
          .join("")}
      </ul>`;
    panel.querySelectorAll("[data-udel]").forEach((b) =>
      b.addEventListener("click", async () => {
        if (!confirm("Удалить клиента?")) return;
        await api("/api/admin/users", {
          method: "DELETE",
          body: JSON.stringify({ id: b.dataset.udel }),
        });
        loadUsers();
      })
    );
    bindCommon();
  }

  async function loadProducts(q = "") {
    const panel = document.getElementById("products-panel");
    const { data } = await api("/api/admin/products?q=" + encodeURIComponent(q));
    const products = data.products || [];
    panel.innerHTML = `
      <div class="panel">
        <form class="admin-inline-form" id="p-search"><input type="search" name="q" placeholder="Поиск…" value="${q.replace(/"/g, "&quot;")}" /><button class="btn btn-small">Найти</button></form>
        <ul class="admin-product-list">
          ${products
            .map(
              (p) => `<li><div class="admin-product-row">
              <div><div class="admin-client-name">${p.name}</div>
              <div class="admin-client-meta">${money(p.price)}${p.hasOverride ? " · изменено" : ""}</div></div>
              <button type="button" class="btn btn-small" data-pedit='${JSON.stringify(p).replace(/'/g, "&#39;")}'>Редактировать</button>
            </div></li>`
            )
            .join("")}
        </ul>
      </div>
      <div class="panel" id="p-edit"><p class="page-lead">Нажмите «Редактировать» у товара.</p></div>`;
    document.getElementById("p-search").onsubmit = (e) => {
      e.preventDefault();
      loadProducts(new FormData(e.target).get("q") || "");
    };
    panel.querySelectorAll("[data-pedit]").forEach((b) =>
      b.addEventListener("click", () => {
        const p = JSON.parse(b.getAttribute("data-pedit"));
        const box = document.getElementById("p-edit");
        box.innerHTML = `<form class="order-form" id="p-save">
          <h2>Редактирование</h2>
          <label>Название<input name="name" value="${p.name.replace(/"/g, "&quot;")}" required /></label>
          <label>Цена<input type="number" name="price" value="${p.price}" required /></label>
          <label>Описание<textarea name="description" rows="5">${p.description || ""}</textarea></label>
          <button class="btn btn-primary">Сохранить</button></form>`;
        document.getElementById("p-save").onsubmit = async (e) => {
          e.preventDefault();
          const fd = Object.fromEntries(new FormData(e.target).entries());
          await api("/api/admin/products", {
            method: "PATCH",
            body: JSON.stringify({ id: p.id, ...fd, price: Number(fd.price) }),
          });
          loadProducts(q);
        };
      })
    );
    bindCommon();
  }

  loadOrders();
})();
