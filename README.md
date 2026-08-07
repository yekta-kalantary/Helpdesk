# Helpdesk

سامانه فارسی مدیریت مشتری، پروژه، تسک و تیکت برای تیم‌های طراحی سایت، سئو و دیجیتال مارکتینگ.

این پروژه یک **Modular Monolith** مبتنی بر Laravel 13 است. API عمومی ندارد و رابط کاربری آن با **Livewire 4 + Blade + Tailwind CSS** پیاده‌سازی شده است.

## نیازمندی‌های اجرا

- PHP 8.4+
- Composer 2
- SQLite و extensionهای `pdo_sqlite` / `sqlite3`
- extensionهای استاندارد Laravel شامل `mbstring`, `openssl`, `ctype`, `json`, `fileinfo`
- `exif` و `fileinfo` برای Media Library
- Node.js فقط برای build کردن assetها در محیط توسعه/CI؛ runtime وب به Node نیاز ندارد.

هیچ Redis، Docker، Elasticsearch، queue worker یا سرویس خارجی اجباری نیست. Livewire در همان Laravel web runtime اجرا می‌شود.

## نصب محلی

```bash
cp .env.example .env
composer run setup
php artisan serve
```

اسکریپت `setup` dependencyها، SQLite، APP_KEY، migration/seed و frontend build را آماده می‌کند.

## معماری UI

هر bounded context، class-based Livewire componentهای خودش را در مسیر زیر نگهداری می‌کند:

```text
app-modules/<module>/src/Presentation/Livewire
```

namespaceهای UI ماژولی هستند؛ برای مثال:

```text
identity::users.index
customers::form
projects::index
tasks::show
tickets::create
settings::smtp
reports::index
```

صفحات، فرم‌ها، فیلترها و write actionهای UI با Livewire انجام می‌شوند. URL و route nameهای اصلی حفظ شده‌اند و navigation داخلی از `wire:navigate` استفاده می‌کند.

Shared Design System در `resources/views/components/ui` قرار دارد و Domain/Application هیچ وابستگی‌ای به Livewire یا Blade ندارند.

تنها exception عمدی، **دانلود attachment** است که برای streaming مستقیم فایل از HTTP GET احراز هویت‌شده استفاده می‌کند. Upload، delete، reply، comment، status و سایر interactionها Livewire هستند.

## نقش‌ها

دو Role سیستمی وجود دارد:

- `admin`: غیرقابل ایجاد، تغییر یا حذف از پنل و دارای دسترسی کامل.
- `customer`: غیرقابل ایجاد، تغییر یا حذف از پنل و مخصوص Client Portal.

تمام Roleهای دیگر و Permissionهای سفارشی از پنل قابل مدیریت هستند.

## ماژول‌ها

| Module | مسئولیت |
| --- | --- |
| `Identity` | Livewire login/logout، کاربران تیم، Role/Permission، Dashboard، Notifications |
| `Customers` | مشتری و حساب Client Portal |
| `Projects` | پروژه، نوع/وضعیت، اعضای تیم و پیشرفت |
| `Tasks` | Livewire Kanban، Assignment، Deadline، زمان، upload، فایل و کامنت داخلی |
| `Tickets` | Livewire تیکت، گفتگو، upload، Assignment و وضعیت پشتیبانی |
| `Reports` | گزارش read-only مشتری، پروژه و تیم |
| `Settings` | تنظیمات SMTP |

ماژول‌های مالی، Website و Subscription عمداً در این نسخه وجود ندارند.

## Client Portal

مشتری پس از فعال شدن Portal می‌تواند پروژه‌های خودش، Taskهای `is_customer_visible`، تیکت‌ها و اعلان‌های خودش را مشاهده کند و روی تیکت‌های خودش پاسخ بدهد. کامنت داخلی Task، زمان تخمینی/مصرف‌شده و Taskهای داخلی برای مشتری نمایش داده نمی‌شوند.

## فایل‌ها

فایل‌های Task و Ticket با `spatie/laravel-medialibrary` روی disk محلی ذخیره می‌شوند. فایل‌ها URL عمومی ندارند. Uploadها از `Livewire\WithFileUploads` و validation server-side عبور می‌کنند و download از route scopeشده stream می‌شود.

## SMTP

SMTP از صفحه Livewire تنظیمات مدیریت می‌شود. رمز SMTP با encrypted settings پکیج `spatie/laravel-settings` نگهداری می‌شود. اگر SMTP غیرفعال باشد، Laravel از mailer محلی `log` استفاده می‌کند.

## مستندات

- `docs/architecture.md`
- `docs/database.md`
- `docs/permissions.md`
- `docs/development.md`
- `docs/installation.md`
- `docs/ui.md`
- `docs/roadmap.md`

## تست و کیفیت

```bash
php artisan view:cache
php artisan test
./vendor/bin/pint --test
npm run build
```

CI روی PHP 8.4 و SQLite، routeها، تمام Blade viewها، Livewire actionهای امنیتی، frontend build و deployable ZIP را اعتبارسنجی می‌کند.
