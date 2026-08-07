# معماری سیستم

## سبک معماری

سیستم یک **Modular Monolith** است: تمام ماژول‌ها در یک Laravel application، یک runtime و یک deployment اجرا می‌شوند، اما مرزهای دامنه در کد حفظ می‌شوند.

Microservice، message broker یا network boundary بین ماژول‌ها وجود ندارد.

## ساختار ماژول

هر bounded context در `app-modules/<module>` قرار می‌گیرد:

```text
app-modules/<module>/
├── composer.json
├── database/
├── resources/
├── routes/
└── src/
    ├── Domain/
    ├── Application/
    ├── Infrastructure/
    ├── Presentation/
    └── <Module>ServiceProvider.php
```

### Domain

شامل مفاهیم مستقل کسب‌وکار است:

- Enumها
- Contract/Portها
- Ruleهای دامنه

Domain نباید به Controller، Blade یا implementationهای Eloquent وابسته باشد.

### Application

Use-caseها و read modelهای هر bounded context:

- `Actions`: عملیات write و orchestration
- `Queries`: read modelهای موردنیاز UI

Application به Contractهای Domain وابسته است، نه implementationهای Infrastructure.

### Infrastructure

جزئیات فنی:

- Eloquent Model/Repository
- Media Library adapter
- Notification adapter
- Spatie Permission adapter
- Settings persistence

این لایه Contractهای Domain را پیاده‌سازی می‌کند.

### Presentation

ورودی/خروجی وب:

- Controller
- Route
- Blade view
- Validation نزدیک مرز HTTP

Controller باید orchestration محدود داشته باشد و business persistence را مستقیم انجام ندهد.

## Dependency Rule

جهت اصلی dependency:

```text
Presentation -> Application -> Domain
                       ^
                       |
               Infrastructure
```

Infrastructure از طریق Service Container به Contractهای Domain bind می‌شود.

## ارتباط بین ماژول‌ها

### Write path

برای write، دسترسی مستقیم یک ماژول به Model داخلی ماژول دیگر ممنوع است. اگر یک workflow واقعاً به write بین دو bounded context نیاز داشت، Contract/Application Service عمومی تعریف شود.

### Read path

برای dashboard/report و form optionهای read-only، query مستقیم روی جداول ماژول دیگر مجاز است، به شرط اینکه:

- read-only باشد؛
- invariant دامنه را تغییر ندهد؛
- در classهای Query/Read Model متمرکز باشد؛
- dependency به Model داخلی ماژول دیگر ایجاد نکند.

این تصمیم یک CQRS-lite برای ساده ماندن monolith است.

## Runtime محلی

پیش‌فرض‌ها:

- Database: SQLite
- Filesystem: local
- Cache: file
- Session: file
- Queue: sync
- Mail: log، مگر SMTP از پنل فعال شود
- Notifications: database

بنابراین هیچ daemon یا سرویس زیرساختی جدا برای اجرای پایه لازم نیست.

## Authorization

Authorization دو سطح دارد:

1. **Capability** با `spatie/laravel-permission` و middlewareهای permission.
2. **Row-level scope** داخل Query/Repository برای اطلاعات مشتری، Task و Ticket.

`admin` با `Gate::before` دسترسی کامل دارد. `admin` و `customer` system role هستند و از مدیریت Roleهای پویا محافظت می‌شوند.

## Localization

Locale پیش‌فرض `fa` است. در Blade/Controller متن قابل نمایش نباید hard-code شود. کلیدها انگلیسی هستند و ترجمه فارسی در `lang/fa` یا `resources/lang/fa` هر ماژول قرار می‌گیرد.

## امنیت فایل

Media روی disk محلی نگهداری می‌شود. دانلود Task/Ticket تنها از route احراز هویت‌شده انجام می‌شود و قبل از stream کردن فایل، scope رکورد بررسی می‌شود.
