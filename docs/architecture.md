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
    │   ├── Livewire/
    │   └── Http/Controllers/   # فقط boundaryهای لازم مثل دانلود فایل
    └── <Module>ServiceProvider.php
```

### Domain

شامل مفاهیم مستقل کسب‌وکار است:

- Enumها
- Contract/Portها
- Ruleهای دامنه

Domain نباید به Livewire، Controller، Blade یا implementationهای Eloquent وابسته باشد.

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

ورودی/خروجی وب بر پایه **Livewire 4 + Blade** است:

- full-page Livewire componentها برای صفحات، فرم‌ها، فیلترها و actionهای تعاملی
- `Route::livewire()` برای routeهای UI
- Blade viewهای ماژولی و Shared `x-ui.*` Kit
- Validation و authorization نزدیک مرز Presentation
- HTTP Controller فقط جایی که response باینری/streaming مناسب‌تر است، مثل دانلود attachment

Livewire component باید orchestration محدود داشته باشد و business persistence را مستقیم پیاده‌سازی نکند؛ از Action/Query/Contractهای Application و Domain استفاده می‌کند.

## Dependency Rule

جهت اصلی dependency:

```text
Livewire/Presentation -> Application -> Domain
                                  ^
                                  |
                          Infrastructure
```

Infrastructure از طریق Service Container به Contractهای Domain bind می‌شود.

## Livewire boundaries

هر ماژول namespace مستقل Livewire دارد؛ مانند `identity::`, `customers::`, `projects::`, `tasks::`, `tickets::`, `reports::`, `settings::`.

قواعد اصلی:

1. route middleware قابلیت کلی صفحه را کنترل می‌کند.
2. هر mutating Livewire action دوباره permission را server-side بررسی می‌کند؛ چون update requestهای Livewire از route اصلی صفحه عبور نمی‌کنند.
3. Row-level scope در Query/Repository باقی می‌ماند و به client state اعتماد نمی‌شود.
4. Propertyهای شناسه‌ای حساس با `#[Locked]` قفل می‌شوند.
5. upload فایل با `WithFileUploads` انجام می‌شود.
6. download فایل از HTTP route احراز هویت‌شده stream می‌شود.

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

- Database: MariaDB
- Filesystem: local
- Cache: file
- Session: file
- Queue: sync
- Mail: log، مگر SMTP از پنل فعال شود
- Notifications: database

بنابراین برای اجرای پایه، MariaDB تنها سرویس زیرساختی اجباری خارج از PHP runtime است. Livewire نیز از همان Laravel web runtime استفاده می‌کند و سرویس جداگانه‌ای نیاز ندارد.

## Authorization

Authorization دو سطح دارد:

1. **Capability** با `spatie/laravel-permission`، route middleware و guard داخل Livewire action.
2. **Row-level scope** داخل Query/Repository برای اطلاعات مشتری، Task و Ticket.

`admin` با `Gate::before` دسترسی کامل دارد. `admin` و `customer` system role هستند و از مدیریت Roleهای پویا محافظت می‌شوند.

## Localization

Locale پیش‌فرض `fa` است. در Blade/Livewire متن قابل نمایش نباید hard-code شود. کلیدها انگلیسی هستند و ترجمه فارسی در `lang/fa` یا `resources/lang/fa` هر ماژول قرار می‌گیرد.

## امنیت فایل

Media روی disk محلی نگهداری می‌شود. upload از Livewire validation عبور می‌کند. دانلود Task/Ticket تنها از route احراز هویت‌شده انجام می‌شود و قبل از stream کردن فایل، scope رکورد بررسی می‌شود.
