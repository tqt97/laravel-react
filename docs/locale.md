# Thiết kế và triển khai tính năng Locale

## Mục tiêu

Cho phép người dùng chuyển đổi giữa tiếng Anh (`en`) và tiếng Việt (`vi`) trên cả public page và authenticated page. Locale phải được áp dụng cho:

- Nội dung backend dịch bằng Laravel Translator.
- Label, heading, accessibility text và thông báo trên React.
- Validation errors.
- Toast notification sau khi đổi locale hoặc thực hiện thao tác.

## Quy ước nguồn translation

Tất cả application translations dùng PHP làm source of truth. Không tạo hoặc chỉnh sửa JSON translation riêng cho từng locale.

- `lang/{locale}/frontend.php` là nguồn duy nhất cho các key hiển thị trên React, kể cả accessibility text của sidebar, key auth và error page như `auth.login`, `common.navigation_menu` và `errors.403.title`.
- Các file PHP theo domain (`common.php`, `authorization.php`, `audit.php`) chứa message backend; `validation.php` giữ đúng cấu trúc Laravel.
- `validation.php` giữ cấu trúc chuẩn của Laravel, bao gồm các key nested như `password.letters` và `attributes.email`.
- `TranslationLoader` chỉ load namespace được khai báo trong `config/locale.php`, flatten key và share cho React qua Inertia.
- Frontend dùng key trong `frontend.php`, ví dụ `common.profile_updated`, `auth.login`, `authorization.roles`.

Khi thêm hoặc sửa text cho React, cập nhật `frontend.php` của cả `en` và `vi`. Khi thêm message Laravel/backend, cập nhật file domain tương ứng của cả hai locale. Không commit bản JSON duplicate.

## Locale được hỗ trợ

Locale được định nghĩa tập trung tại [`app/Enums/Locale.php`](../app/Enums/Locale.php):

```php
enum Locale: string
{
    case ENGLISH = 'en';
    case VIETNAMESE = 'vi';
}
```

Frontend dùng type và constant tương ứng tại [`resources/js/types/locale.ts`](../resources/js/types/locale.ts).

## Quy tắc chọn locale

Middleware [`SetLocale`](../app/Http/Middleware/SetLocale.php) chọn locale cho request guest theo thứ tự:

1. Locale trong session.
2. Cookie `app_locale`.
3. `config('app.fallback_locale')`.

Authenticated user dùng middleware alias `auth.locale` sau `auth`, vì vậy locale trong database được ưu tiên hơn session.

Locale luôn được kiểm tra qua `Locale::tryFrom()` trước khi gọi `App::setLocale()`.

## Lưu trữ

Migration thêm cột `users.locale` với giá trị mặc định là `en`.

- Authenticated user: lưu locale trong database, session và cookie.
- Guest: chỉ đọc locale từ session/cookie; không có quyền cập nhật locale.

Route đổi locale:

```http
PATCH /settings/locale
```

Payload:

```json
{
    "locale": "vi"
}
```

Request được validate bằng `Rule::enum(Locale::class)`.

## Backend implementation

### Middleware

`SetLocale` đọc session/cookie cho guest. Alias `auth.locale` trỏ tới `SetAuthenticatedLocale` và luôn được đặt sau `auth` để đọc đúng locale từ user, sau đó đồng bộ session và cookie.

### Controller và Action

[`LocaleController`](../app/Http/Controllers/Settings/LocaleController.php) nhận locale đã validate, gọi [`UpdateLocale`](../app/Actions/Settings/UpdateLocale.php), cập nhật session và set locale hiện tại.

Toast dùng Inertia flash và backend dùng key namespace trong file PHP:

```php
Inertia::flash('toast', [
    'type' => 'success',
    'message' => __('common.language_updated'),
]);
```

Không nên dùng `with('success')` cho toast nếu frontend đang lắng nghe `flash.toast`. `withInput()` và `withErrors()` vẫn dành cho form validation.

### Validation messages

Validation translations nằm tại:

- [`lang/en/validation.php`](../lang/en/validation.php)
- [`lang/vi/validation.php`](../lang/vi/validation.php)

Laravel resolve validation message theo `app()->getLocale()` tại thời điểm request chạy. Vì vậy middleware locale phải chạy trước Fortify/Form Request/controller validation.

Khi thêm validation rule mới, cần cập nhật cả hai file, bao gồm cả các key nested như `password.letters`, `password.mixed`, `password.numbers`, `password.symbols`, `password.uncompromised` và các biến thể `min.string`/`max.string` nếu rule có sử dụng.

Tên field hiển thị cho người dùng được khai báo trong `validation.attributes`. Không nên dịch trực tiếp ở React; `InputError` nên hiển thị message đã được Laravel resolve.

## Inertia translation props

[`HandleInertiaRequests`](../app/Http/Middleware/HandleInertiaRequests.php) share các props:

- `locale` — locale hiện tại.
- `translations` — các key trong namespace `frontend`, được load qua `TranslationLoader`, cache theo locale + namespace và fingerprint file.

Translation cache tự thay đổi khi nội dung hoặc metadata của file PHP thay đổi. Có thể xóa application cache nếu cần làm mới ngay trong môi trường production.

## Persistence policy

```text
Authenticated: users.locale → session.locale + app_locale cookie
Guest: session.locale → app_locale cookie → APP_FALLBACK_LOCALE
```

Khi user đổi locale trong Settings, cả DB, session và cookie được cập nhật. Khi logout, session có thể bị hủy nhưng cookie vẫn giữ ngôn ngữ cho lần truy cập guest tiếp theo. Khi đăng nhập trên thiết bị khác, locale được đọc từ DB rồi đồng bộ vào session/cookie của thiết bị đó.

User mới khi đăng ký nhận locale hiện tại do middleware đã resolve; guest không cần gửi trường `locale` trong form.

## Frontend implementation

[`useTranslation`](../resources/js/hooks/use-translation.ts) cung cấp:

```tsx
const { locale, t, tc } = useTranslation();

<Button>{t('Save')}</Button>;
```

`t()` hỗ trợ interpolation và `tc()` hỗ trợ hậu tố `.one`/`.other`. Missing key được cảnh báo trong development. `useLocaleFormat()` cung cấp format ngày, số và tiền tệ theo locale hiện tại.

### Language switcher

[`LanguageSwitcher`](../resources/js/components/language-switcher.tsx) dùng Wayfinder:

```tsx
import { update } from '@/routes/locale';

router.patch(update.url(), { locale: nextLocale });
```

Switcher chỉ được dùng trong appearance settings. Route đổi locale yêu cầu authenticated user; guest không có quyền cập nhật locale. Guest chỉ sử dụng locale đã có trong session/cookie.

## Translation parity

```bash
php artisan translations:check
```

Command kiểm tra key thiếu giữa locale mặc định và locale khác trên toàn bộ PHP translation namespace; nên chạy trong CI.

Command này flatten các key nested của PHP translation, nên sẽ phát hiện trường hợp một locale thiếu `validation.password.*` dù key cha `password` vẫn tồn tại.

## Triển khai production

Thực hiện các bước sau sau mỗi lần thêm hoặc thay đổi locale/translation:

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan translations:check
npm ci
npm run build
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Sau khi deploy, restart PHP-FPM/worker nếu hệ thống đang giữ tiến trình dài hạn. Không cần cấu hình `TRANSLATION_CACHE_VERSION`; translation tự invalidates theo fingerprint file. Nếu application cache vẫn giữ dữ liệu cũ, chạy `php artisan cache:clear`.

Kiểm tra nhanh locale và validation trên production:

1. Đăng nhập bằng user có `users.locale = vi`.
2. Gửi một form sai dữ liệu, ví dụ bỏ trống tên hoặc nhập email không hợp lệ.
3. Xác nhận message trả về là tiếng Việt và không còn chuỗi tiếng Anh mặc định.
4. Chuyển sang `en` và lặp lại để kiểm tra cả hai locale.

Nếu validation vẫn trả về tiếng Anh, kiểm tra lần lượt `users.locale`, session/cookie `app_locale`, middleware `auth.locale`/`SetLocale`, cache config và sự tồn tại của key trong `lang/{locale}/validation.php`.

## Quy trình thêm locale mới

Khi thêm locale, thực hiện các bước:

1. Thêm case vào `app/Enums/Locale.php`.
2. Cập nhật frontend `Locale`/`LOCALES`.
3. Thêm thư mục `lang/{locale}`.
4. Bổ sung `lang/{locale}/frontend.php` cho React và PHP domain translations cho backend.
5. Bổ sung PHP validation translations, giữ đúng cấu trúc nested.
6. Kiểm tra migration/database constraint nếu có.
7. Regenerate Wayfinder nếu route thay đổi:

    ```bash
    php artisan wayfinder:generate --with-form
    ```

8. Bổ sung feature test cho locale mới.

## Kiểm tra

```bash
npm run types:check
npm run build
php artisan test tests/Feature/Settings/LocaleUpdateTest.php
php artisan test
php artisan translations:check
```

Các hành vi tối thiểu cần test:

- Guest không thể đổi locale.
- Guest đọc được locale hợp lệ từ session/cookie.
- Authenticated user đổi locale và database được cập nhật.
- Locale không hợp lệ bị reject.
- Locale session không hợp lệ fallback về `en`.
- Toast đổi locale được hiển thị.
- Cả English và Vietnamese đều có translation key cần thiết.
