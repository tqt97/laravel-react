# Laravel React Application

Ứng dụng được xây dựng với Laravel, Inertia.js và React. Project cung cấp nền tảng xác thực, quản lý tài khoản, phân quyền và các thiết lập ứng dụng.

## Stack

- PHP 8.3+
- Laravel 13
- Inertia.js 3
- React 19
- TypeScript
- Laravel Fortify
- Laravel Wayfinder
- Tailwind CSS 4
- PHPUnit, PHPStan/Larastan và Laravel Pint

## Tính năng

- Đăng ký, đăng nhập và đăng xuất.
- Xác minh email.
- Quên và đặt lại mật khẩu.
- Xác nhận mật khẩu cho khu vực bảo mật.
- Two-factor authentication (2FA/TOTP).
- Passkey/WebAuthn.
- Quản lý thông tin profile.
- Đổi mật khẩu và xóa tài khoản.
- Quản lý giao diện ứng dụng.
- Đa ngôn ngữ English/Vietnamese.
- Phân quyền và permission workspace.
- Audit log và các màn hình quản trị liên quan.

## Cấu trúc chính

- `app/` — Controllers, Actions, Models, Middleware, Form Requests và Enums.
- `routes/` — Web routes và settings routes.
- `resources/js/` — React pages, layouts, components và Wayfinder routes.
- `lang/` — Translation files cho backend và frontend.
- `database/` — Migrations, factories và seeders.
- `tests/` — PHPUnit feature/unit tests.
- `docs/` — Tài liệu triển khai và thiết kế tính năng.

## Cài đặt

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build
```

Trên Windows, có thể sao chép `.env.example` thành `.env` thủ công nếu `cp` không khả dụng.

## Phát triển local

```bash
composer run dev
```

Hoặc chạy riêng frontend:

```bash
npm run dev
```

## Kiểm tra chất lượng

```bash
npm run check
npm run types:check
composer run lint:check
php artisan test
```

## Tài liệu

- [Thiết kế và triển khai tính năng locale](docs/locale.md)
- [Laravel documentation](https://laravel.com/docs)
- [Inertia.js documentation](https://inertiajs.com/)
