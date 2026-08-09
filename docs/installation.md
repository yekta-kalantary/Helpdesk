# نصب و استقرار

## حداقل Runtime

- PHP 8.4+
- Composer 2 برای نصب dependencyها
- MariaDB 11.x
- PHP extensions موردنیاز Laravel و Media Library: `pdo_mysql`, `mbstring`, `openssl`, `ctype`, `json`, `fileinfo`, `exif`
- Web server با document root روی پوشه `public/`

Redis، Supervisor، queue worker و Docker اجباری نیستند. MariaDB باید به‌عنوان database server در دسترس application باشد.

## آماده‌سازی MariaDB برای Development

با یک کاربر دارای دسترسی مدیریتی وارد MariaDB شوید و دیتابیس‌های development و testing را بسازید:

```sql
CREATE DATABASE helpdesk CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE helpdesk_testing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE USER 'helpdesk'@'localhost' IDENTIFIED BY 'helpdesk';
CREATE USER 'helpdesk'@'127.0.0.1' IDENTIFIED BY 'helpdesk';

GRANT ALL PRIVILEGES ON helpdesk.* TO 'helpdesk'@'localhost';
GRANT ALL PRIVILEGES ON helpdesk_testing.* TO 'helpdesk'@'localhost';
GRANT ALL PRIVILEGES ON helpdesk.* TO 'helpdesk'@'127.0.0.1';
GRANT ALL PRIVILEGES ON helpdesk_testing.* TO 'helpdesk'@'127.0.0.1';

FLUSH PRIVILEGES;
```

مقادیر `helpdesk/helpdesk` فقط default توسعه هستند. در production حتماً password قوی و کاربر اختصاصی تعریف کنید.

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
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force
npm install
npm run build
```

Migrationهای `spatie/laravel-settings` از طریق migrationهای معمول Laravel بارگذاری و با همان `php artisan migrate` اجرا می‌شوند؛ command جداگانه‌ای لازم نیست.

## تنظیم دیتابیس

تنظیم پیش‌فرض توسعه:

```env
DB_CONNECTION=mariadb
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=helpdesk
DB_USERNAME=helpdesk
DB_PASSWORD=helpdesk
```

در production این مقادیر را با credentials واقعی MariaDB جایگزین کنید و از account مدیریتی مانند `root` برای application استفاده نکنید.

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
php artisan db:seed --force
php artisan optimize
```

اگر assetها قبلاً در CI build نشده‌اند، قبل از deploy `npm run build` نیز اجرا شود.

تنظیمات پیشنهادی production:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.example
DB_CONNECTION=mariadb
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=helpdesk
DB_USERNAME=helpdesk_app
DB_PASSWORD=<strong-secret>
```

## Backup

برای backup حداقل دیتابیس MariaDB و attachmentها را نگهداری کنید:

```bash
mariadb-dump --single-transaction --routines --triggers -u helpdesk_app -p helpdesk > helpdesk.sql
```

همراه dump دیتابیس، مسیر `storage/app/` نیز باید backup شود.

## ارتقا

در هر release:

1. کد جدید را دریافت کنید.
2. `composer install` اجرا کنید.
3. migrationهای Laravel را اجرا کنید؛ migrationهای Settings نیز از همین مسیر اجرا می‌شوند.
4. Seeder canonical permissions را اجرا کنید.
5. cacheها را rebuild کنید.

Seeder idempotent طراحی شده است و Roleهای سیستمی را به permission set canonical برمی‌گرداند.
