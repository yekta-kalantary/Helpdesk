# Helpdesk

سامانه فارسی مدیریت مخاطب، پروژه و تسک با معماری **Modular Monolith** مبتنی بر Laravel 13 و Livewire 4.

## ماژول‌ها

Business modules:

| Module | مسئولیت |
| --- | --- |
| `Contacts` | اطلاعات عمومی و تماس مخاطبین |
| `Projects` | پروژه‌های مخاطب یا داخلی، اعضای تیم، وضعیت و پیشرفت |
| `Tasks` | Kanban، Assignment، Deadline، Time tracking، Comment و Attachment use cases |

Platform modules:

| Module | مسئولیت |
| --- | --- |
| `Identity` | Login/Logout، Account، کارکنان، Role و Permission |
| `Media` | ذخیره، خواندن، حذف و metadata فایل به‌صورت shared/polymorphic |

ماژول‌های `Customers`، `Tickets`، `Reports`، `Settings`، Notification Center و Client Portal در این نسخه وجود ندارند.

## Modular ownership

هر module مالک مدل Eloquent، migration، repository و use-caseهای context خودش است. مدل‌های `Contact` و `User` داخل moduleهای Contacts و Identity قرار دارند و domain model در `app/Models` نگهداری نمی‌شود.

Dependencyهای اصلی:

```text
Identity ─────> Contacts
Projects ─────> Contacts + Identity
Tasks ────────> Projects + Identity + Media
Media ────────> Spatie Media Library
```

## مدل هویتی

`contacts` تنها منبع اطلاعات شخصی است:

- `first_name`
- `last_name`
- `gender`
- `email`
- `mobile`
- `province`
- `city`
- `address`
- `postal_code`

`users` فقط Account ورود و Authorization است و با `contact_id` به Contact متصل می‌شود. هر Contact می‌تواند بدون User وجود داشته باشد.

Project می‌تواند `contact` یا `internal` باشد. Task همیشه متعلق به Project است و assignee باید User فعال و عضو همان Project باشد.

## Shared Media

Spatie Media Library فقط implementation داخلی module `Media` است. Tasks مستقیماً به Spatie وابسته نیست و attachmentها را از `MediaManager` استفاده می‌کند.

در حال حاضر Tasks collection `attachments` دارد. همین capability می‌تواند برای Contact/Project و سایر moduleهای آینده بدون dependency به Tasks استفاده شود.

## نصب محلی

نیازمندی‌های اصلی:

- PHP 8.4+
- Composer 2
- MariaDB 11.x
- Node.js 22 برای build frontend

```bash
cp .env.example .env
composer run setup
php artisan serve
```

برای ارتقای دیتابیس قبلی:

```bash
composer install
php artisan optimize:clear
php artisan migrate --force
php artisan db:seed --force
```

Migrationهای اجراشده rewrite نمی‌شوند. Upgradeهای ساختاری با forward migration و backfill انجام می‌شوند.

## UI

صفحات اصلی:

```text
/contacts
/projects
/tasks
/users   # کارکنان
/roles
```

Contact detail شامل:

```text
General Info
Contact Info
Account Settings
```

Account Settings از نظر authorization متعلق به Identity است: مشاهده آن `users.view` و mutation آن `users.create/users.update` می‌خواهد.

## Role و Permission

فقط `admin` یک Role سیستمی و immutable است. سایر Roleها قابل مدیریت‌اند. Permissionها در `PermissionCatalog` و در گروه‌های زیر تعریف می‌شوند:

```text
contacts
projects
tasks
identity
```

## کیفیت

CI هر دو مسیر را بررسی می‌کند:

1. upgrade از یک schema legacy نماینده با `php artisan migrate`
2. ساخت کامل schema جدید با `migrate:fresh`

و سپس:

```bash
composer validate --no-check-publish
php artisan db:seed --force
php artisan route:list
php artisan view:cache
php artisan test
./vendor/bin/pint --test
npm run build
```

مستندات تکمیلی در `docs/` قرار دارند.
