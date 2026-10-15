# Chứng Nhận Tăng Ni (Laravel 13 + Filament 4)

Chuyển từ NukeViet 4. Giữ nguyên URL công khai để QR code trên thẻ đã cấp vẫn hoạt động:

- `/official/{alias-giới-đàn}/{họ-tên}-{id}` — tra cứu **chỉ theo `id`** (alias/tên sai vẫn mở được)
- `/official`, `/official/page-N`, `/official/{giới-đàn}`, `/official/search?q=&part_id=`
- `/api_public.php?action=list|detail|search|categories|parts|info` — JSON tương thích bản cũ
- `/index.php?nv=official&op=...` và `/` → 301 sang URL mới
- `/uploads/...` — dùng lại thư mục uploads cũ (ảnh, PDF, nền thẻ)
- `/admin` — quản trị (Filament): Thẻ tăng ni, Tỉnh thành

## Triển khai
1. `composer install --no-dev`, copy `.env`, đặt `APP_URL=https://chungnhantangni.ghpgvn.vn` (**bắt buộc**: dùng để sinh URL trong QR).
2. `php artisan migrate`, rồi nhập dữ liệu: nạp file SQL NukeViet vào DB tạm, đặt `LEGACY_DB_DATABASE`, chạy `php artisan nukeviet:import --fresh`. Id được giữ nguyên.
3. Chép thư mục `uploads/` cũ vào `public/uploads/` (ở local là symlink).
4. Trỏ document root vào `public/`. Tạo admin: `php artisan make:filament-user`.
