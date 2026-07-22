-- ГравЕкб SQLite schema

CREATE TABLE IF NOT EXISTS users (
  id TEXT PRIMARY KEY,
  name TEXT NOT NULL,
  email TEXT NOT NULL UNIQUE,
  phone TEXT NOT NULL DEFAULT '',
  password_hash TEXT NOT NULL,
  created_at TEXT NOT NULL,
  company_name TEXT NOT NULL DEFAULT '',
  inn TEXT NOT NULL DEFAULT '',
  ogrn TEXT NOT NULL DEFAULT '',
  bik TEXT NOT NULL DEFAULT '',
  checking_account TEXT NOT NULL DEFAULT ''
);

CREATE TABLE IF NOT EXISTS sessions (
  token TEXT PRIMARY KEY,
  user_id TEXT NOT NULL,
  created_at TEXT NOT NULL,
  expires_at TEXT NOT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS admin_sessions (
  token TEXT PRIMARY KEY,
  created_at TEXT NOT NULL,
  expires_at TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS categories (
  slug TEXT PRIMARY KEY,
  name TEXT NOT NULL,
  price_from REAL NOT NULL DEFAULT 0,
  data_json TEXT NOT NULL DEFAULT '{}'
);

CREATE TABLE IF NOT EXISTS products (
  id TEXT PRIMARY KEY,
  slug TEXT NOT NULL UNIQUE,
  name TEXT NOT NULL,
  description TEXT NOT NULL DEFAULT '',
  price REAL NOT NULL DEFAULT 0,
  image TEXT,
  category TEXT NOT NULL,
  subcategory TEXT,
  source_path TEXT NOT NULL DEFAULT '',
  in_stock INTEGER NOT NULL DEFAULT 1,
  unlimited INTEGER NOT NULL DEFAULT 0,
  hidden INTEGER NOT NULL DEFAULT 0
);

CREATE INDEX IF NOT EXISTS idx_products_category ON products(category);
CREATE INDEX IF NOT EXISTS idx_products_subcategory ON products(subcategory);
CREATE INDEX IF NOT EXISTS idx_products_name ON products(name);

CREATE TABLE IF NOT EXISTS product_overrides (
  product_id TEXT PRIMARY KEY,
  name TEXT,
  description TEXT,
  price REAL,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS orders (
  id TEXT PRIMARY KEY,
  number TEXT NOT NULL UNIQUE,
  type TEXT NOT NULL DEFAULT 'order',
  user_id TEXT,
  name TEXT NOT NULL DEFAULT '',
  phone TEXT NOT NULL DEFAULT '',
  email TEXT NOT NULL DEFAULT '',
  comment TEXT NOT NULL DEFAULT '',
  topic TEXT,
  total REAL NOT NULL DEFAULT 0,
  status TEXT NOT NULL DEFAULT 'new',
  email_sent INTEGER NOT NULL DEFAULT 0,
  invoice_sent INTEGER NOT NULL DEFAULT 0,
  invoice_number TEXT,
  upd_number TEXT,
  created_at TEXT NOT NULL,
  company_name TEXT NOT NULL DEFAULT '',
  inn TEXT NOT NULL DEFAULT '',
  ogrn TEXT NOT NULL DEFAULT '',
  bik TEXT NOT NULL DEFAULT '',
  checking_account TEXT NOT NULL DEFAULT ''
);

CREATE INDEX IF NOT EXISTS idx_orders_created ON orders(created_at);
CREATE INDEX IF NOT EXISTS idx_orders_user ON orders(user_id);

CREATE TABLE IF NOT EXISTS order_items (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  order_id TEXT NOT NULL,
  item_id TEXT NOT NULL DEFAULT '',
  slug TEXT,
  name TEXT NOT NULL,
  qty INTEGER NOT NULL DEFAULT 1,
  price REAL NOT NULL DEFAULT 0,
  image TEXT,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_order_items_order ON order_items(order_id);

CREATE TABLE IF NOT EXISTS counters (
  name TEXT PRIMARY KEY,
  value INTEGER NOT NULL DEFAULT 0
);
