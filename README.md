# GDTC AP Tracker

Ứng dụng nội bộ quản lý bằng chứng thi công WiFi AP, xây dựng bằng Laravel 12, Blade, TailwindCSS và Laravel Reverb.

## Chức năng

- Ba tổ đăng nhập bằng mã truy cập và session.
- Một AP tương ứng một bản ghi, không giới hạn số AP định trước.
- AP đã lắp yêu cầu ba ảnh; AP bị chặn yêu cầu lý do và ảnh hiện trường.
- Cho phép sửa bản ghi và thay ảnh tùy chọn.
- Trang danh sách AP hôm nay riêng cho từng tổ.
- Quản trị lọc, xem, sửa, xóa, xuất CSV và theo dõi realtime.
- Lưu bản ghi vẫn thành công khi Reverb không khả dụng.

## Cài đặt local

Yêu cầu PHP 8.3+, Composer, Node.js và SQLite hoặc MySQL.

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm install
npm run build
```

Chạy ứng dụng:

```bash
php artisan serve
php artisan queue:work
php artisan reverb:start
```

Các URL mặc định:

- `/t/team-1`: `vnpt1`
- `/t/team-2`: `vnpt2`
- `/t/team-3`: `vnpt3`
- `/admin`: `vnptadmin`

Phải thay các mã truy cập trước khi sử dụng production. Logo nằm tại `public/images/logo.jpg`.

## Triển khai bằng FlashPanel

Tạo một Laravel site riêng với PHP 8.3, document root `public`, MySQL database riêng và SSL.

Deployment script (giữ nguyên phần checkout do FlashPanel tạo):

```bash
composer install --no-dev --prefer-dist --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan storage:link || true
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart
```

Tạo hai daemon trong FlashPanel:

```bash
php artisan queue:work --sleep=3 --tries=3 --timeout=120
php artisan reverb:start
```

Reverb nên bind vào `127.0.0.1:8081`. Cấu hình Nginx của site cần proxy WebSocket `/app` đến cổng này. Không commit `.env`; thiết lập biến môi trường production bằng Environment Editor của FlashPanel.

## Kiểm thử

```bash
php artisan test
npm run build
```
