# Helpdesk

سامانه فارسی مدیریت مشتری، پروژه، تسک و تیکت برای تیم‌های طراحی سایت، سئو و دیجیتال مارکتینگ.

این پروژه یک **Modular Monolith** مبتنی بر Laravel است. API عمومی ندارد و رابط کاربری آن با Blade + Tailwind CSS پیاده‌سازی شده است.

## نیازمندی‌های اجرا

- PHP 8.4+
- Composer 2
- SQLite و extensionهای `pdo_sqlite` / `sqlite3`
- extensionهای استاندارد Laravel شامل `mbstring`, `openssl`, `ctype`, `json`, `fileinfo`
- `exif` و `fileinfo` برای Media Library
- Node.js فقط برای build کردن assetها در محیط توسعه/CI؛ runtime وب به Node نیاز ندارد.

هیچ Redis، Docker، Elasticsearch، queue worker یا سرویس خارجی اجباری نیست.

## نصب محلی

```bash
cp .env.example .env
composer run setup
php artisan serve
```

اسکریپت `setup` این مراحل را انجام می‌دهد:

1. نصب dependencyهای Composer
2. ایجاد فایل SQLite در `database/database.sqlite`
3. تولید `APP_KEY`
4. اجرای migrationهای Laravel
5. اجرای migrationهای `spatie/laravel-settings`
6. اجرای Seeder نقش‌ها و دسترسی‌ها
7. نصب و build کردن assetهای frontend

## حساب Admin اولیه

مقادیر از `.env` خوانده می‌شوند:

```env
HELPDESK_ADMIN_NAME="Administrator"
HELPDESK_ADMIN_EMAIL="admin@example.com"
HELPDESK_ADMIN_PASSWORD="password"
```

**قبل از استقرار واقعی حتماً این مقادیر را تغییر دهید.**

دو Role سیستمی وجود دارد:

- `admin`: غیرقابل ایجاد، تغییر یا حذف از پنل و دارای دسترسی کامل.
- `customer`: غیرقابل ایجاد، تغییر یا حذف از پنل و مخصوص Client Portal.

تمام Roleهای دیگر و Permissionهای سفارشی از پنل قابل مدیریت هستند.

## ماژول‌ها

| Module | مسئولیت |
| --- | --- |
| `Identity` | ورود وب، کاربران تیم، Role/Permission، Dashboard، Notifications |
| `Customers` | مشتری و حساب Client Portal |
| `Projects` | پروژه، نوع/وضعیت، اعضای تیم و پیشرفت |
| `Tasks` | Kanban، Assignment، Deadline، زمان، فایل و کامنت داخلی |
| `Tickets` | تیکت، گفتگو، فایل، Assignment و وضعیت پشتیبانی |
| `Reports` | گزارش read-only مشتری، پروژه و تیم |
| `Settings` | تنظیمات SMTP |

ماژول‌های مالی، Website و Subscription عمداً در این نسخه وجود ندارند.

## Client Portal

مشتری پس از فعال شدن Portal می‌تواند:

- پروژه‌های خودش را ببیند.
- فقط Taskهایی را ببیند که `is_customer_visible` هستند.
- تیکت‌های خودش را ایجاد، مشاهده و پاسخ دهد.
- اعلان‌های خودش را مشاهده کند.

کامنت داخلی Task، زمان تخمینی/مصرف‌شده و Taskهای داخلی برای مشتری نمایش داده نمی‌شوند.

## فایل‌ها

فایل‌های Task و Ticket با `spatie/laravel-medialibrary` روی disk محلی ذخیره می‌شوند. فایل‌ها URL عمومی ندارند و دانلود از route احراز هویت‌شده و scopeشده انجام می‌شود.

## SMTP

SMTP از پنل تنظیمات مدیریت می‌شود. رمز SMTP با قابلیت encrypted settings پکیج `spatie/laravel-settings` رمزنگاری می‌شود. اگر SMTP غیرفعال باشد، Laravel از mailer محلی `log` استفاده می‌کند.

## توسعه

مستندات فنی در پوشه `docs/` قرار دارند:

- `docs/architecture.md`
- `docs/database.md`
- `docs/permissions.md`
- `docs/development.md`
- `docs/installation.md`
- `docs/roadmap.md`

## تست و کیفیت

```bash
php artisan test
./vendor/bin/pint --test
npm run build
```

CI همین مسیر را روی PHP 8.4 و SQLite اعتبارسنجی می‌کند.
