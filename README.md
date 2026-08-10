# Helpdesk

سامانه فارسی مدیریت مخاطب، پروژه و تسک با معماری **Modular Monolith** مبتنی بر Laravel 13 و Livewire 4.

## ماژول‌ها

فقط چهار ماژول در هسته سیستم وجود دارد:

| Module | مسئولیت |
| --- | --- |
| `Identity` | Login/Logout، حساب‌های کاربری، Role و Permission |
| `Contacts` | اطلاعات عمومی، اطلاعات تماس و Account Settings مخاطبین |
| `Projects` | پروژه‌های مخاطب یا داخلی، اعضای تیم، وضعیت و پیشرفت |
| `Tasks` | Kanban، Assignment، Deadline، Time tracking، Comment و Attachment |

ماژول‌های `Customers`، `Tickets`، `Reports`، `Settings`، Notification Center و Client Portal در این نسخه وجود ندارند.

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

`users` فقط حساب ورود و دسترسی است و با `contact_id` به Contact متصل می‌شود. هر Contact می‌تواند بدون User وجود داشته باشد.

Project می‌تواند از نوع `contact` یا `internal` باشد. Project نوع `contact` با `projects.contact_id` مستقیماً به Contact متصل است. Task همیشه متعلق به Project است.

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

## UI

رابط کاربری با Livewire 4 + Blade + Tailwind CSS پیاده‌سازی شده است. صفحات اصلی:

```text
/contacts
/projects
/tasks
/users
/roles
```

Contact detail سه بخش دارد:

```text
General Info
Contact Info
Account Settings
```

جستجوی Contact و انتخاب Contact در Project مستقیماً روی دیتابیس و بر اساس نام، نام خانوادگی، ایمیل و موبایل انجام می‌شود.

## Role و Permission

فقط `admin` یک Role سیستمی و immutable است. سایر Roleها قابل مدیریت‌اند. Permissionها در کد و در `PermissionCatalog` تعریف می‌شوند و متعلق به یکی از گروه‌های زیر هستند:

```text
contacts
projects
tasks
identity
```

## فایل‌های Task

Attachmentهای Task با `spatie/laravel-medialibrary` روی disk محلی ذخیره می‌شوند و download از route احراز هویت‌شده انجام می‌شود.

## کیفیت

CI روی PHP 8.4 و MariaDB موارد زیر را بررسی می‌کند:

```bash
composer validate --no-check-publish
php artisan migrate:fresh --force
php artisan db:seed --force
php artisan route:list
php artisan view:cache
php artisan test
./vendor/bin/pint --test
npm run build
```

مستندات تکمیلی در `docs/` قرار دارند.
