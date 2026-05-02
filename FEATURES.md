# CashBoard — Tính năng v0.1

## Actors

| Actor | Mô tả |
|---|---|
| **Guest** | Chưa đăng nhập |
| **Staff** | Nhân viên, đã đăng nhập và được gán vào store |
| **Owner** | Chủ quán, đã đăng nhập |
| **System** | Tự động (background job, webhook) |

---

## Auth

| Tính năng | Guest | Staff | Owner |
|---|:---:|:---:|:---:|
| Đăng nhập | ✅ | — | — |
| Đăng ký | ✅ | — | — |
| Đăng xuất | — | ✅ | ✅ |

---

## Store

| Tính năng | Guest | Staff | Owner |
|---|:---:|:---:|:---:|
| Tạo store mới | — | — | ✅ |
| Danh sách stores | — | — | ✅ |
| Sửa thông tin store | — | ✅ | ✅ |
| Activate store (chuyển store đang dùng) | — | ✅ | ✅ |

> **Lưu ý:** `stores.edit / update / activate` hiện chưa có middleware `owner` — staff có thể sửa store nếu có `store.access`.

---

## Dashboard

| Tính năng | Guest | Staff | Owner |
|---|:---:|:---:|:---:|
| KPI cards (tổng thu, chi, số giao dịch) | — | ✅ | ✅ |
| Biểu đồ dòng tiền 30 ngày | — | ✅ | ✅ |
| Phân tích nguồn giao dịch (cash / import / email) | — | ✅ | ✅ |

---

## Nhân viên & Ca làm

| Tính năng | Guest | Staff | Owner |
|---|:---:|:---:|:---:|
| Xem danh sách nhân viên | — | ✅ | ✅ |
| Thêm nhân viên | — | — | ✅ |
| Sửa thông tin nhân viên | — | — | ✅ |
| Bật / Tắt nhân viên | — | — | ✅ |
| Mở ca | — | ✅ | ✅ |
| Đóng ca | — | ✅ | ✅ |

---

## Giao dịch

| Tính năng | Guest | Staff | Owner |
|---|:---:|:---:|:---:|
| Nhập tiền mặt thủ công | — | ✅ | ✅ |
| Import CSV / XLSX (với column mapping) | — | ✅ | ✅ |
| Nhận email ngân hàng qua Mailgun webhook | ✅ ¹ | — | — |
| Danh sách giao dịch (filter, phân trang) | — | ✅ | ✅ |
| Xóa giao dịch | — | ✅ | ✅ |

> ¹ Public endpoint — xác thực bằng HMAC token. Actor thực tế là **Mailgun**, không phải user.

---

## Báo cáo & Xuất file

| Tính năng | Guest | Staff | Owner |
|---|:---:|:---:|:---:|
| Xuất Excel danh sách giao dịch | — | ✅ | ✅ |
| Xuất Excel daily summary | — | ✅ | ✅ |

---

## Nền (Background / System)

| Tính năng | Trigger |
|---|---|
| Tính daily summary tự động | Sau mỗi transaction create / delete (`RecalculateDailySummaryJob`) |
| Parse email ngân hàng thành giao dịch | Inbound email webhook → queue job |

---

## Ma trận tổng hợp

| Nhóm | Guest | Staff | Owner |
|---|:---:|:---:|:---:|
| Auth | ✅ | ✅ | ✅ |
| Store | — | một phần | ✅ |
| Dashboard | — | ✅ | ✅ |
| Nhân viên (xem) | — | ✅ | ✅ |
| Nhân viên (quản lý) | — | — | ✅ |
| Ca làm | — | ✅ | ✅ |
| Giao dịch | — | ✅ | ✅ |
| Báo cáo / Export | — | ✅ | ✅ |
