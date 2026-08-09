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

## Migrationهای production

پروژه live است. از این نقطه به بعد migration اجراشده **immutable** محسوب می‌شود و نباید برای تغییر schema بازنویسی شود.

برای هر تغییر schema/data:

1. migration جدید بسازید؛ migration تاریخی را edit نکنید.
2. تغییرات ناسازگار را به‌صورت چندمرحله‌ای انجام دهید: `add -> backfill -> validate -> enforce/drop`.
3. اگر ستون/رابطه جدید جایگزین داده قبلی می‌شود، backfill تمام رکوردهای موجود الزامی است.
4. قبل از enforce کردن `NOT NULL`، unique یا FK جدید، migration باید وجود رکوردهای map نشده را بررسی کند و در صورت مشکل fail شود.
5. حذف ستون یا جدول داده‌دار بدون مقصد archive/backfill مجاز نیست. ابتدا application باید خواندن/نوشتن آن را متوقف کند و cleanup در deployment جداگانه انجام شود.
6. migration داده‌ای مخرب باید forward-only و با توضیح صریح باشد؛ rollback نباید source of truth قبلی و جدید را همزمان برگرداند.

Migrationها باید با MariaDB سازگار باشند و از SQL یا behavior اختصاصی SQLite استفاده نکنند. CI تمام migration/seed/testها را روی MariaDB اجرا می‌کند.

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

Role مشتری صرفاً permission set است. scope مشتری باید از `users.person_id -> customers.person_id` resolve شود؛ هویت مشتری از Role استنتاج نمی‌شود.

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

تست‌ها با دیتابیس MariaDB مجزای `helpdesk_testing` اجرا می‌شوند. این دیتابیس باید قبل از اجرای test suite وجود داشته باشد و فقط برای تست استفاده شود.

## Style

```bash
./vendor/bin/pint
php artisan test
npm run build
```

قبل از merge هر سه باید موفق باشند.
