# راهنمای توسعه

## اصل اول: سیستم Monolith باقی می‌ماند

ماژول جدید به معنی سرویس جدید نیست. همه ماژول‌ها در همان Laravel application، همان process و همان database اجرا می‌شوند.

## ایجاد ماژول جدید

ساختار پیشنهادی:

```text
app-modules/billing/
├── composer.json
├── database/migrations/
├── resources/lang/{fa,en}/
├── resources/views/
├── routes/web.php
└── src/
    ├── Domain/
    │   ├── Contracts/
    │   └── Enums/
    ├── Application/
    │   ├── Actions/
    │   └── Queries/
    ├── Infrastructure/
    │   └── Models/
    ├── Presentation/Http/Controllers/
    └── BillingServiceProvider.php
```

اگر generator پکیج `internachi/modular` برای نیاز فعلی مناسب بود می‌توان از Artisan آن استفاده کرد؛ در غیر این صورت همین convention دستی حفظ شود.

Module package را در `composer.json` ریشه به‌صورت `modules/<name>: "*"` اضافه کنید. path repository ریشه تمام `app-modules/*` را discover می‌کند.

## Ruleهای کدنویسی

### Controller

Controller مسئول این موارد است:

- HTTP validation
- authorization boundary
- تبدیل request به input use-case
- redirect/view response

Controller نباید business query پیچیده یا چند write مستقل Eloquent داشته باشد.

### Action

Action یک use-case مشخص را اجرا می‌کند. Transactionهایی که چند persistence operation دارند در Action یا Repository مناسب نگهداری شوند.

### Repository/Port

Application به interface در Domain وابسته است. Eloquent implementation در Infrastructure bind می‌شود.

### Query

برای list/dashboard/report و form optionهای read-only، Query class ایجاد کنید. Cross-module read فقط در این لایه و به‌صورت read-only مجاز است.

## Eloquent

Modelهای Eloquent implementation detail لایه Infrastructure هستند. از import کردن Model داخلی یک ماژول داخل Domain/Application ماژول دیگر خودداری کنید.

`App\Models\User` یک shared identity model در application shell است و فعلاً استثنای آگاهانه سیستم است.

## ترجمه

تمام stringهای UI باید با translation key فراخوانی شوند:

```php
__('tasks::messages.new_task')
```

در Blade متن فارسی hard-code نکنید. برای هر کلید حداقل `fa` و `en` نگهداری شود.

## Authorization Checklist

برای هر endpoint جدید:

1. آیا route permission دارد؟
2. آیا action روی رکورد متعلق به همان user/customer است؟
3. آیا ID ارسالی می‌تواند به resource ماژول/مشتری دیگری اشاره کند؟
4. آیا download فایل قبل از خواندن path scope را بررسی می‌کند؟
5. آیا اطلاعات داخلی در Client Portal leak می‌شود؟

## Client Portal

Role مشتری صرفاً یک permission set نیست. scope مشتری باید همیشه از `customers.user_id` resolve شود.

Taskهای داخلی default هستند. فقط Task با `is_customer_visible=true` در Portal دیده می‌شود.

## Media

برای attachment جدید از Media Library استفاده کنید و disk را `local` نگه دارید. فایل private باید از controller scoped دانلود شود. `getUrl()` برای فایل‌های private استفاده نشود.

## Settings

تنظیمات runtime قابل تغییر را در ماژول Settings نگه دارید. Secretها با encrypted setting ذخیره شوند. Secret موجود هرگز به input HTML برگردانده نشود.

## تست

حداقل برای هر قابلیت:

- happy path
- permission denied
- row-level isolation
- validation مهم
- regression برای bug امنیتی

تست‌ها با SQLite memory اجرا می‌شوند.

## Style

```bash
./vendor/bin/pint
php artisan test
npm run build
```

قبل از merge هر سه باید موفق باشند.
