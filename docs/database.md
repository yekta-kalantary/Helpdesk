# مدل داده

## ERD سطح بالا

```text
people
  ├── users (0..1, account/login)
  └── customers (0..1, only when type=customer)

users
  ├── model_has_roles -> roles -> role_has_permissions -> permissions
  ├── project_user -> projects
  ├── tasks.created_by / tasks.assigned_to
  ├── task_comments
  ├── tickets.created_by / tickets.assigned_to
  ├── ticket_messages
  └── notifications

customers
  ├── person_id -> people (unique)
  ├── projects
  └── tickets

projects
  ├── customer_id -> customers
  ├── project_user -> users
  ├── tasks
  └── tickets

settings
```

## منبع حقیقت اطلاعات افراد

`people` تنها منبع اطلاعات هویتی است:

- `type`: `customer | employee`
- `first_name`
- `last_name`
- `email`
- `mobile`

نام، ایمیل و موبایل در `users` یا `customers` تکرار نمی‌شوند.

### `users`

`users` فقط حساب احراز هویت و مجوزدهی است و با `person_id` به `people` متصل می‌شود.

- Employee باید User داشته باشد.
- Customer می‌تواند User نداشته باشد.
- ایجاد User برای Customer فقط دسترسی Portal را فعال می‌کند و هویت Customer را تغییر نمی‌دهد.
- غیرفعال‌کردن دسترسی Employee یا Customer فقط `users.is_active` را false می‌کند؛ User و Person حذف نمی‌شوند.
- Role فقط Authorization است؛ Customer/Employee بودن از `people.type` و رابطه دامنه تعیین می‌شود.

### `customers`

`customers` اطلاعات مخصوص رابطه مشتری را نگه می‌دارد:

- `person_id`: یکتا و متعلق به Person از نوع `customer`
- `notes`

Customer وضعیت دامنه‌ای ندارد و مسیر حذف نیز ندارد. Person و Customer برای حفظ history همیشه باقی می‌مانند. اگر Portal User وجود داشته باشد، قطع دسترسی فقط آن User را غیرفعال می‌کند.

ستون‌های legacy مانند `status` یا `deleted_at` ممکن است در دیتابیس‌هایی که migrationهای تاریخی را اجرا کرده‌اند وجود داشته باشند. Application جدید آن‌ها را برای حذف Customer استفاده نمی‌کند و داده‌های موجود بدون migration مستقل و migration path امن دست‌کاری نمی‌شوند.

حساب Portal از `users.person_id = customers.person_id` resolve می‌شود و FK مستقیمی از Customer به User وجود ندارد.

### `projects`

- متعلق به یک Customer
- `type`: `website_design | seo | digital_marketing | support | other`
- `status`: `planning | active | paused | completed | cancelled`
- soft delete
- اعضای تیم از `project_user`

### `tasks`

- متعلق به یک Project
- assignee اختیاری و باید Employee فعال باشد
- creator اجباری
- `priority`: `low | medium | high | urgent`
- `status`: `todo | in_progress | review | done | cancelled`
- `is_customer_visible`: پیش‌فرض `false`
- زمان تخمینی و مصرف‌شده برحسب دقیقه
- soft delete

`is_customer_visible` boundary اطلاعات داخلی و Client Portal است. همه queryهای مشتری باید آن را enforce کنند.

### `task_comments`

کامنت داخلی تیم است و در Client Portal نمایش داده نمی‌شود.

### `tickets`

- متعلق به Customer
- Project اختیاری
- creator و assignee
- assignee باید Employee فعال باشد
- category، priority، status
- soft delete

### `ticket_messages`

Thread تیکت به‌صورت messageهای مستقل ذخیره می‌شود. هر message می‌تواند media داشته باشد.

### `media`

جدول استاندارد Media Library برای attachmentهای Task و TicketMessage. فایل فیزیکی روی disk `local` است.

### `notifications`

Database notification استاندارد Laravel.

### `settings`

Repository پکیج `spatie/laravel-settings`. رمز SMTP encrypted setting است.

## قواعد حذف و غیرفعال‌سازی

- Customer و Employee از Application حذف نمی‌شوند.
- غیرفعال‌کردن Customer Portal فقط `users.is_active=false` می‌کند و Person/Customer/User را نگه می‌دارد.
- غیرفعال‌کردن Employee فقط `users.is_active=false` می‌کند و Person/User را نگه می‌دارد.
- Project و Task و Ticket همچنان سیاست حذف مستقل خودشان را دارند.
- رکوردهای legacy که قبلاً soft-delete شده‌اند در این تغییر restore یا rewrite نمی‌شوند.

## تغییر schema در production

از زمان live شدن پروژه، migration اجراشده immutable است و ویرایش نمی‌شود. هر تغییر schema با migration جدید انجام می‌شود.

برای تغییرات داده‌دار، ترتیب امن الزامی است:

1. اضافه‌کردن schema سازگار و nullable/default-safe در صورت نیاز.
2. backfill داده‌های موجود به مقصد جدید.
3. اعتبارسنجی کامل بودن backfill و جلوگیری از ادامه migration در صورت وجود رکورد map نشده.
4. enforce کردن constraintهای جدید.
5. حذف schema قدیمی فقط زمانی که داده‌ای بدون مقصد باقی نمانده باشد.

migration سازگاری پروفایل‌های قبلی همین الگو را برای انتقال داده‌ها به `people` استفاده می‌کند و forward-only است؛ rollback نباید دوباره دو source of truth ایجاد کند.
