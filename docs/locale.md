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
3. Ngôn ngữ đầu tiên được hỗ trợ trong header `Accept-Language` nếu `LOCALE_ACCEPT_LANGUAGE=true`.
4. `config('app.fallback_locale')`.

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
- `timezone` — timezone hợp lệ của user, hoặc `config('app.timezone')` nếu guest/giá trị không hợp lệ.
- `translations` — các key trong namespace `frontend`, được load qua `TranslationLoader`, cache theo locale + namespace và fingerprint file.

Translation cache tự thay đổi khi nội dung hoặc metadata của file PHP thay đổi. Có thể xóa application cache nếu cần làm mới ngay trong môi trường production.

## Persistence policy

```text
Authenticated: users.locale → session.locale + app_locale cookie
Guest: session.locale → app_locale cookie → Accept-Language (tùy cấu hình) → APP_FALLBACK_LOCALE
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

## Kiến trúc tổng thể

### Một resolver cho toàn bộ backend

[`LocaleResolver`](../app/Support/Locale/LocaleResolver.php) là nơi duy nhất quyết định locale. Cả `SetLocale` và `SetAuthenticatedLocale` đều dùng resolver này để tránh mỗi middleware có một thứ tự ưu tiên khác nhau.

Thứ tự thực tế:

```text
Authenticated: users.locale → session → cookie → Accept-Language (tùy cấu hình) → APP_FALLBACK_LOCALE
Guest: session → app_locale cookie → Accept-Language (tùy cấu hình) → APP_FALLBACK_LOCALE
```

`auth.locale` phải luôn đứng sau `auth`. Middleware này chỉ áp dụng cho route authenticated; guest không có quyền gọi route `PATCH /settings/locale`.

### Session, cookie và database

- Guest có thể nhận locale từ session, cookie hoặc `Accept-Language`, nhưng không được ghi locale vào database.
- User authenticated đổi locale tại Settings. Giá trị mới được ghi vào `users.locale`, session và cookie.
- Khi logout, session có thể bị hủy; cookie vẫn giúp guest giữ lựa chọn trước đó.
- Khi user đăng nhập trên thiết bị khác, `users.locale` được ưu tiên và đồng bộ lại vào session/cookie.
- Cookie locale có tên và thời hạn cấu hình tại `config/locale.php`; cookie này phải nằm ngoài danh sách encrypted cookies vì middleware cần đọc giá trị của nó.

### Cấu hình môi trường

```dotenv
APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_TIMEZONE=UTC
LOCALE_ACCEPT_LANGUAGE=true
LOCALE_COOKIE_NAME=app_locale
LOCALE_COOKIE_MINUTES=525600
```

`LOCALE_ACCEPT_LANGUAGE=false` hữu ích khi ứng dụng phải luôn dùng locale đã chọn trong session/cookie và không muốn phụ thuộc ngôn ngữ trình duyệt.

### Translation backend và React

- `lang/{locale}/validation.php`: message validation theo cấu trúc Laravel, có thể nested.
- `lang/{locale}/common.php`, `authorization.php`, `audit.php`: message backend/domain.
- `lang/{locale}/frontend.php`: catalog duy nhất cho React.
- `TranslationLoader`: đọc `frontend.php`, flatten key và share qua Inertia prop `translations`.
- Không thêm bản dịch vào JSON hoặc hard-code text UI khi key translation đã tồn tại.

Trong React, dùng:

```tsx
const { locale, t, tc } = useTranslation();
const { formatDate, formatNumber, formatCurrency } = useLocaleFormat();

t('auth.login');
tc('items', count);
formatDate(value);
formatCurrency(amount, 'VND');
```

`resources/js/types/global.d.ts` khai báo `locale`, `timezone` và `translations` trong shared Inertia props. Khi thêm shared prop mới, phải cập nhật cả middleware share và type augmentation.

### Placeholder và validation

Placeholder phải giữ nguyên tên giữa các locale, ví dụ `:count`, `:attribute`, `:max`. Không đổi tên placeholder khi dịch vì Laravel sẽ không thể thay giá trị.

Validation phải chạy sau middleware locale. Với route authenticated, dùng:

```php
Route::middleware(['auth', 'auth.locale'])->group(function (): void {
    // protected routes
});
```

Không dịch lại validation message ở React; frontend chỉ hiển thị lỗi đã được Laravel resolve theo `app()->getLocale()`.

### Timezone và format

Locale và timezone là hai khái niệm độc lập:

- Locale quyết định ngôn ngữ, format số và currency.
- Timezone quyết định cách hiển thị ngày giờ.
- `User::preferredTimezone()` kiểm tra timezone có nằm trong `timezone_identifiers_list()` hay không và fallback về `APP_TIMEZONE`/`config('app.timezone')`.
- `useLocaleFormat().formatDate()` dùng timezone đã được share từ backend.

Migration timezone mặc định user hiện tại về `UTC`. Nếu mở Settings cho user đổi timezone, request mới phải validate bằng danh sách timezone chuẩn và cập nhật `users.timezone`; field này đã được cho phép mass assignment.

### Error pages Inertia

Các request Inertia có status `403`, `404`, `419`, `429`, `500`, `503` được render bởi [`resources/js/pages/errors/error.tsx`](../resources/js/pages/errors/error.tsx). Page chỉ nhận status code, lấy nội dung từ `frontend.php` và không hiển thị exception details trong production.

### Kiểm tra CI và cache

Chạy các lệnh sau trong CI:

```bash
php artisan translations:check
composer run types:check
npm run check
npm run types:check
php artisan test
```

`translations:check` so sánh flattened keys giữa locale mặc định và các locale còn lại. Một placeholder có thể xuất hiện nhiều lần trong cùng message và đó là hành vi hợp lệ của Laravel. Translation cache dùng fingerprint metadata của file PHP; không cần `TRANSLATION_CACHE_VERSION`. Sau deploy có thể chạy `php artisan optimize:clear` nếu application cache còn dữ liệu cũ.

### Thêm locale mới

1. Thêm case vào `app/Enums/Locale.php`.
2. Cập nhật `resources/js/types/locale.ts` và danh sách locale frontend.
3. Tạo `lang/{locale}/frontend.php`, `validation.php` và các file domain cần thiết.
4. Duy trì cùng key và placeholder với locale mặc định.
5. Kiểm tra `config('app.fallback_locale')`, cookie và `Accept-Language`.
6. Bổ sung feature test cho resolver, validation và Inertia props.
7. Chạy `php artisan translations:check`, PHPUnit, PHPStan và frontend checks.
