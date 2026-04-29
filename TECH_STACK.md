# TECH_STACK.md
## Project: SaaS Gom & Quản Lý Dòng Tiền Cho Quán Nhỏ

> Triết lý cốt lõi:
> - KHÔNG phải POS
> - KHÔNG xử lý thanh toán
> - KHÔNG làm ngân hàng / fintech
> - CHỈ gom – chuẩn hóa – hiển thị – báo cáo dòng tiền
> - Mục tiêu duy nhất: **chủ quán biết hôm nay bao nhiêu tiền**

---

## 1. Tổng Quan Kiến Trúc

Hệ thống là một **SaaS CRUD + Import + Aggregation**.

Đặc điểm:
- Không real-time cứng
- Không yêu cầu API ngân hàng ở MVP
- Chấp nhận dữ liệu semi-auto (email, file, nhập tay)
- Tối ưu cho tốc độ phát triển + phí vận hành thấp

---

## 2. Backend

### Framework
- **Laravel (PHP)**

Lý do chọn:
- Phù hợp bài toán CRUD + SaaS
- Hệ sinh thái mạnh cho:
  - Auth
  - Queue
  - Scheduler
  - Mail parser
  - Import / Export Excel
- Dev Việt dễ tiếp cận, dễ maintain
- Time-to-market nhanh

Backend chịu trách nhiệm:
- User / Store / Employee / Shift management
- Nhận & xử lý dữ liệu giao dịch
- Chuẩn hóa giao dịch từ nhiều nguồn
- Tổng hợp dữ liệu cho dashboard & báo cáo

---

## 3. Database

### Database chính
- **PostgreSQL** (ưu tiên)
- MySQL chấp nhận được cho MVP nhỏ

Nguyên tắc:
- Schema phẳng, dễ aggregate
- Không mô phỏng POS / order phức tạp
- Có thể lưu dữ liệu thô (raw data) nếu cần

#### Core tables (dự kiến)
- `stores`
- `transactions`
- `sources` (bank_qr / wallet / card / cash)
- `shifts`
- `employees`
- `daily_summaries` (cache trước để dashboard nhanh)

---

## 4. Frontend

### Framework
- **Vue 3 + Vite**

Lý do chọn:
- Admin / Dashboard rất phù hợp
- Ít boilerplate, dễ đọc
- Phù hợp team nhỏ / solo dev

UI tập trung:
- Doanh thu hôm nay
- So sánh hôm qua
- Theo ca / theo nguồn
- Bảng giao dịch
- Export Excel

Có thể dùng:
- SPA nhẹ
- Hoặc Laravel Blade + Vue components

---

## 5. UI / Styling

- **TailwindCSS**

Nguyên tắc UI:
- Sạch
- Dễ đọc
- Không mang cảm giác fintech / ngân hàng
- Ưu tiên số liệu hơn hiệu ứng

---

## 6. Data Input (Cốt lõi của MVP)

### 6.1 Email Parser
- Forward email thông báo ngân hàng về inbox hệ thống
- Laravel Mail Listener + parser
- Regex / template theo từng ngân hàng

Lưu ý:
- Đây là **feature chủ động**, không phải nợ kỹ thuật
- Semi-auto được chấp nhận ở MVP

### 6.2 Import File
- CSV / XLSX
- Map cột thủ công lần đầu
- Lưu cấu hình import cho lần sau

### 6.3 Tiền mặt
- Nhập tổng theo ca / ngày
- Không yêu cầu chi tiết từng bill

---

## 7. Queue & Job

- Laravel Queue
- Redis (hoặc DB queue ở MVP)

Dùng cho:
- Parse email
- Import file lớn
- Recalculate daily summary
- Cleanup & async tasks

---

## 8. Auth & Phân Quyền

Mức tối thiểu:
- Owner (chủ quán)
- Staff (nhân viên nhập ca / tiền mặt)

Không triển khai RBAC phức tạp ở MVP.

---

## 9. Reporting & Export

- Xuất Excel:
  - Ngày
  - Tuần
  - Tháng
- Phục vụ:
  - Chủ quán
  - Kế toán
  - Đối soát nội bộ

---

## 10. Deployment & Infrastructure

### MVP
- VPS đơn (10–20 USD / tháng)
- Nginx
- PHP-FPM
- PostgreSQL
- S3-compatible storage (backup, export)

Không dùng:
- Kubernetes
- Microservices
- Multi-region
- Infrastructure phức tạp

---

## 11. Những Thứ CỐ Ý Không Làm Ở MVP

- API ngân hàng chính thức
- Real-time streaming
- POS / Order management
- Đối soát 100%
- Pháp lý fintech

> API ngân hàng là **phần thưởng sau khi có user**, không phải điểm bắt đầu.

---

## 12. Nguyên Tắc Phát Triển

- Hoàn thành MVP < 2 tháng
- Có user thật trước khi tối ưu
- Ưu tiên vấn đề vận hành của chủ quán, không ưu tiên sự “đẹp kỹ thuật”

> “Nếu quán cần API → họ đã lớn.  
> Nếu quán còn cộng tay → đây là khách hàng của mình.”

---