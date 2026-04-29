# CashBoard — CLAUDE.md

SaaS quản lý dòng tiền cho nhà hàng/quán nhỏ tại Việt Nam. Phiên bản hiện tại: **v0.1**.

---

## Stack

- **Laravel 13** / PHP 8.5 · Docker (Laravel Sail)
- **PostgreSQL** — datetime lưu UTC, hiển thị theo timezone của store
- **TailwindCSS v4** + Vite (không có `tailwind.config.js`, dùng `@import 'tailwindcss'`)
- **Queue**: database driver (`QUEUE_CONNECTION=database`)
- **File parsing**: `openspout/openspout` ^5.7 (maatwebsite/excel không tương thích PHP 8.5)

---

## Chạy dự án

Project chạy hoàn toàn trong Docker qua Laravel Sail. **Không chạy PHP/composer/artisan trực tiếp trên host.**

```bash
# Khởi động
./vendor/bin/sail up -d

# Artisan
./vendor/bin/sail artisan migrate

# Frontend (lần đầu)
./vendor/bin/sail npm install
./vendor/bin/sail npm run dev   # dev server (hot reload)
# hoặc
./vendor/bin/sail npm run build # build 1 lần
```

> node_modules phải được install bên trong container (Linux). Nếu đã install trên Windows host thì xoá đi và reinstall trong Sail để tránh lỗi native binding.

---

## Kiến trúc quan trọng

### Store context
Session-based: `StoreContext::id()`, `StoreContext::current()`, `StoreContext::activate(Store)`.
Mọi query đều phải filter theo `store_id = StoreContext::id()`.

### Middleware
- `owner` → `EnsureOwner` (abort 403 nếu không phải owner)
- `store.access` → `EnsureStoreAccess` (auto-set active store; staff dùng Employee lookup)

### Datetime
- Lưu DB: UTC
- Hiển thị: `->timezone(session('active_store_timezone'))`
- Query range: `Carbon::parse($date, $tz)->startOfDay()->utc()`

### Daily Summary
- Idempotent UPSERT qua `DailySummaryService::recalculate(storeId, summaryDate)`
- Trigger: `RecalculateDailySummaryJob` (dispatch sau mỗi transaction create/delete)
- Fallback live-query nếu chưa có cache: `getOrCalculate()`

### Email parser (inbound webhook)
- Plugin pattern: mỗi ngân hàng là 1 class extends `BaseEmailParser`
- Factory: `EmailParserFactory` duyệt qua danh sách parser, trả về `ParsedTransaction` hoặc null
- Webhook Mailgun: `POST /api/inbound-email/{token}`, HMAC verify, luôn trả 200
- CSRF excluded cho `/api/inbound-email/*` trong `bootstrap/app.php`

---

## Database — migrations (theo thứ tự)

| File | Nội dung |
|------|----------|
| `000001` | Thêm `role` enum (owner/staff) vào `users` |
| `000002` | Bảng `stores` |
| `000003` | Bảng `employees` |
| `000004` | Bảng `shifts` |
| `000005` | Bảng `import_batches` |
| `000006` | Bảng `transactions` (SoftDeletes, unique store_id+reference_id) |
| `000007` | Bảng `daily_summaries` (unique store_id+summary_date) |
| `000008` | Thêm `inbound_email_token` vào stores + bảng `failed_email_parses` |

---

## Features đã hoàn thành (v0.1)

| Task | Mô tả |
|------|-------|
| TASK-01 | Auth (login/register, role owner/staff) |
| TASK-02 | Layout, guest/app template, Tailwind v4 |
| TASK-03 | Store management CRUD + activate |
| TASK-04 | Employee & shift management |
| TASK-05 | Cash entry form (nhập tiền mặt thủ công) |
| TASK-06 | CSV/XLSX import với column mapping |
| TASK-07 | Inbound email webhook (Mailgun) + bank parsers |
| TASK-08 | Transaction listing (filter, paginate, delete) |
| TASK-09 | Dashboard (KPI, 30-day chart, source breakdown) |
| TASK-10 | Excel export (transactions + daily summary) |
| TASK-11 | Queue & background jobs scaffold |
| TASK-12 | Daily summary aggregation service |

---

## Routes tóm tắt

```
guest:              /login, /register
auth+owner:         /stores/create, POST /stores
auth+store.access:  /dashboard, /transactions, /cash, /import, /export, /employees, /shifts, /stores/*
auth+store.access+owner: /stores (index)
public:             POST /api/inbound-email/{token}
```

---

## Conventions

- Khi cần chạy lệnh shell (artisan, composer, npm): **hỏi user chạy qua Sail**, không tự chạy.
- Không commit trực tiếp lên main nếu chưa test — dự án dùng branch master.
- Mỗi task commit riêng với prefix `feat(...)`, `fix(...)`.
