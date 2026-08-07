# نصب و استقرار

## حداقل Runtime

- PHP 8.4+
- Composer 2 برای نصب dependencyها
- SQLite
- PHP extensions موردنیاز Laravel و Media Library: `pdo_sqlite`, `sqlite3`, `mbstring`, `openssl`, `ctype`, `json`, `fileinfo`, `exif`
- Web server با document root روی پوشه `public/`

Redis، Supervisor، queue worker، Docker و database server خارجی اجباری نیستند.

## نصب توسعه

```bash
git clone <repository>
cd Helpdesk
cp .env.example .env
composer run setup
php artisan serve
```

## نصب دستی

```bash
composer install
cp .env.example .env
touch database/database.sqlite
php artisan key:generate
php artisan migrate --force
php artisan settings:migrate
php artisan db:seed --force
npm install
npm run build
```

## تنظیم Admin

پیش از Seeder یا اولین deploy این مقادیر را در `.env` تنظیم کنید:

```env
HELPDESK_ADMIN_NAME="Your Name"
HELPDESK_ADMIN_EMAIL="admin@example.com"
HELPDESK_ADMIN_PASSWORD="use-a-strong-password"
```

از password نمونه در production استفاده نکنید.

## دسترسی نوشتن

Web server باید روی این مسیرها write داشته باشد:

```text
storage/
bootstrap/cache/
database/database.sqlite
```

Attachmentها در `storage/app` قرار می‌گیرند و public symlink برای آن‌ها لازم نیست.

## Frontend

Node.js فقط مرحله build است. پس از وجود `public/build`، runtime درخواست‌های وب به Node/Vite server نیاز ندارد.

برای development:

```bash
npm run dev
```

برای deploy:

```bash
npm run build
```

## SMTP

پیش‌فرض `MAIL_MAILER=log` است و سیستم بدون SMTP اجرا می‌شود. بعد از ورود با Admin، از پنل تنظیمات SMTP را فعال کنید.

Secret SMTP داخل settings repository به‌صورت encrypted نگهداری می‌شود و به `.env` وابسته نیست.

## Production Checklist

```bash
composer install --no-dev --prefer-dist --optimize-autoloader
php artisan migrate --force
php artisan settings:migrate
php artisan db:seed --force
php artisan optimize
```

اگر assetها قبلاً در CI build نشده‌اند، قبل از deploy `npm run build` نیز اجرا شود.

تنظیمات پیشنهادی production:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.example
```

## Backup

برای نسخه local-first حداقل این دو بخش backup شوند:

- `database/database.sqlite`
- `storage/app/`

این دو شامل داده‌های عملیاتی و attachmentها هستند.

## ارتقا

در هر release:

1. کد جدید را دریافت کنید.
2. `composer install` اجرا کنید.
3. migrationهای Laravel را اجرا کنید.
4. `settings:migrate` اجرا کنید.
5. Seeder canonical permissions را اجرا کنید.
6. cacheها را rebuild کنید.

Seeder idempotent طراحی شده است و Roleهای سیستمی را به permission set canonical برمی‌گرداند.
