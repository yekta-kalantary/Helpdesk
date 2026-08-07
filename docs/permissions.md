# نقش‌ها و دسترسی‌ها

## System Roles

دو Role ثابت هستند:

### `admin`

- از پنل قابل ایجاد، تغییر یا حذف نیست.
- Seeder تمام Permissionهای canonical را روی آن sync می‌کند.
- `Gate::before` دسترسی کامل Admin را تضمین می‌کند.
- از مسیر مدیریت کاربران تیم قابل ویرایش یا حذف نیست.

### `customer`

- فقط توسط workflow ایجاد/ویرایش Customer Portal تخصیص داده می‌شود.
- از پنل Role و User Management قابل تخصیص دستی نیست.
- Permissionهای Portal به‌صورت canonical از Seeder sync می‌شوند.

## Permissionهای هسته

| Domain | Permission |
| --- | --- |
| Customer | `customers.view` |
| Customer | `customers.create` |
| Customer | `customers.update` |
| Customer | `customers.delete` |
| Project | `projects.view` |
| Project | `projects.create` |
| Project | `projects.update` |
| Project | `projects.delete` |
| Task | `tasks.view` |
| Task | `tasks.create` |
| Task | `tasks.update` |
| Task | `tasks.delete` |
| Task | `tasks.comment` |
| Task | `tasks.manage_all` |
| Ticket | `tickets.view` |
| Ticket | `tickets.create` |
| Ticket | `tickets.reply` |
| Ticket | `tickets.manage` |
| Ticket | `tickets.delete` |
| Ticket | `tickets.manage_all` |
| User | `users.view` |
| User | `users.create` |
| User | `users.update` |
| User | `users.delete` |
| RBAC | `roles.view` |
| RBAC | `roles.create` |
| RBAC | `roles.update` |
| RBAC | `roles.delete` |
| Reports | `reports.view` |
| Settings | `settings.manage` |
| Notifications | `notifications.view` |

## Customer Role Matrix

Customer به‌صورت پیش‌فرض فقط این دسترسی‌ها را دارد:

```text
projects.view
tasks.view
tickets.view
tickets.create
tickets.reply
notifications.view
```

Permission به‌تنهایی برای Portal کافی نیست. Repository/Queryها row-level scope را نیز enforce می‌کنند:

- Project فقط متعلق به Customer فعلی.
- Task فقط متعلق به Project مشتری و `is_customer_visible = true`.
- Ticket فقط متعلق به Customer فعلی.

## Dynamic Roles

Roleهای غیرسیستمی از پنل قابل ایجاد هستند؛ مثال:

```text
project-manager
seo-specialist
developer
content-manager
```

نام Role و Permission در دیتابیس انگلیسی و machine-readable نگهداری می‌شود.

## `manage_all`

`tasks.manage_all` و `tickets.manage_all` permissionهای scope هستند:

- بدون `tasks.manage_all`: کاربر فقط Taskهای assign‌شده به خودش یا Projectهایی را می‌بیند که عضو آن‌ها است.
- بدون `tickets.manage_all`: کاربر فقط Ticketهای assign‌شده یا Ticketهای Projectهایی را می‌بیند که عضو آن‌ها است.

این Permissionها باید فقط به Roleهای مدیریتی داده شوند.

## افزودن Permission جدید

1. Permission را به Seeder canonical اضافه کنید اگر جزء هسته محصول است.
2. route/controller را با middleware یا `@can` محافظت کنید.
3. اگر داده row-level است، scope را در Repository/Query اعمال کنید؛ middleware به‌تنهایی کافی نیست.
4. تست دسترسی مثبت و منفی اضافه کنید.
