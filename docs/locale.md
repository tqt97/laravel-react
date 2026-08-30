# Thiết kế và triển khai tính năng Locale

## Mục tiêu

Cho phép người dùng chuyển đổi giữa tiếng Anh (`en`) và tiếng Việt (`vi`) trên cả public page và authenticated page. Locale phải được áp dụng cho:

- Nội dung backend dịch bằng Laravel Translator.
- Label, heading, accessibility text và thông báo trên React.
- Validation errors.
- Toast notification sau khi đổi locale hoặc thực hiện thao tác.

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

Middleware [`SetLocale`](../app/Http/Middleware/SetLocale.php) chọn locale theo thứ tự:

1. Locale của user đã đăng nhập.
2. Locale được lưu trong session.
3. `config('app.locale')`.
4. `en` nếu giá trị không hợp lệ.

Locale luôn được kiểm tra qua `Locale::tryFrom()` trước khi gọi `App::setLocale()`.

## Lưu trữ

Migration thêm cột `users.locale` với giá trị mặc định là `en`.

- Authenticated user: lưu locale trong database và session.
- Guest: chỉ lưu locale trong session.

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

`SetLocale` được append vào web middleware stack để locale có hiệu lực trước khi Inertia share props và render response.

### Controller và Action

[`LocaleController`](../app/Http/Controllers/Settings/LocaleController.php) nhận locale đã validate, gọi [`UpdateLocale`](../app/Actions/Settings/UpdateLocale.php), cập nhật session và set locale hiện tại.

Toast dùng Inertia flash:

```php
Inertia::flash('toast', [
    'type' => 'success',
    'message' => __('Language updated.'),
]);
```

Không nên dùng `with('success')` cho toast nếu frontend đang lắng nghe `flash.toast`. `withInput()` và `withErrors()` vẫn dành cho form validation.

### Validation messages

Validation translations nằm tại:

- [`lang/en/validation.php`](../lang/en/validation.php)
- [`lang/vi/validation.php`](../lang/vi/validation.php)

Khi thêm validation rule mới, cần cập nhật cả hai file.

## Inertia translation props

[`HandleInertiaRequests`](../app/Http/Middleware/HandleInertiaRequests.php) share các props:

- `locale` — locale hiện tại.
- `translations` — các key/value từ file JSON tương ứng trong `lang/{locale}`.

Translation JSON được cache theo locale. Khi cập nhật translation trong production, cần clear cache:

```bash
php artisan cache:clear
```

## Frontend implementation

[`useTranslation`](../resources/js/hooks/use-translation.ts) cung cấp:

```tsx
const { locale, t } = useTranslation();

<Button>{t('Save')}</Button>;
```

`useTranslation()` được gọi tại component cần dịch. Layout cha không tự truyền hook xuống page con; page/component nào render label thì component đó dùng hook.

### Language switcher

[`LanguageSwitcher`](../resources/js/components/language-switcher.tsx) dùng Wayfinder:

```tsx
import { update } from '@/routes/locale';

router.patch(update.url(), { locale: nextLocale });
```

Switcher được dùng trên welcome, login, register và appearance settings. Request có trạng thái `processing` để ngăn click lặp.

## Quy trình thêm locale mới

Khi thêm locale, thực hiện các bước:

1. Thêm case vào `app/Enums/Locale.php`.
2. Cập nhật frontend `Locale`/`LOCALES`.
3. Thêm thư mục `lang/{locale}`.
4. Bổ sung JSON translations.
5. Bổ sung backend validation translations.
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
```

Các hành vi tối thiểu cần test:

- Guest đổi locale và locale được lưu vào session.
- Authenticated user đổi locale và database được cập nhật.
- Locale không hợp lệ bị reject.
- Locale session không hợp lệ fallback về `en`.
- Toast đổi locale được hiển thị.
- Cả English và Vietnamese đều có translation key cần thiết.
