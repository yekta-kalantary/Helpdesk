# معماری

پروژه یک **Modular Monolith** است. سه ماژول Business، یک ماژول Identity/Access و یک ماژول Shared/Platform دارد:

```text
Business
├── Contacts
├── Projects
└── Tasks

Identity & Access
└── Identity

Shared / Platform
└── Media
```

## مالکیت

هر ماژول مالک مدل Eloquent، migration، repository و use-caseهای مربوط به context خودش است:

| Module | Models / Schema |
| --- | --- |
| `Contacts` | `Contact`, `contacts` |
| `Identity` | `User`, `users`, Role/Permission schema |
| `Projects` | `Project`, `projects`, `project_user` |
| `Tasks` | `Task`, `TaskComment`, `tasks`, `task_comments` |
| `Media` | shared media storage contract/implementation و `media` |

مدل‌های business/authentication داخل `app/Models` نگهداری نمی‌شوند. namespace canonical هر مدل متعلق به module مالک آن است.

## وابستگی ماژول‌ها

```text
Identity ─────> Contacts
Projects ─────> Contacts
Projects ─────> Identity
Tasks ────────> Projects
Tasks ────────> Identity
Tasks ────────> Media
Media ────────> Spatie Media Library
```

Contacts برای Account Settings به Identity implementation وابسته نمی‌شود. Contract حساب کاربری را Contacts تعریف می‌کند و Identity آن را implement/bind می‌کند؛ بنابراین dependency cycle ایجاد نمی‌شود.

## روابط اصلی داده

```text
Contact 1 ─── 0..1 User
Contact 1 ─── N Project
Project N ─── N User
Project 1 ─── N Task
Task 0..1 ─── User (assignee)
Task 1 ────── User (creator)
```

- `Contact` منبع واحد اطلاعات شخصی است.
- `User` فقط Authentication/Authorization و وضعیت Account را نگه می‌دارد.
- Project نوع `contact` مستقیماً `contact_id` دارد؛ نوع `internal` Contact ندارد.
- Assignee تسک باید User فعال و عضو همان Project باشد.

## Shared Media

Media یک capability عمومی است و متعلق به Tasks نیست. Spatie Media Library فقط داخل module `Media` شناخته می‌شود.

Business moduleها business authorization و collection semantics خودشان را نگه می‌دارند و عملیات storage را به `MediaManager` واگذار می‌کنند. در حال حاضر Tasks از collection `attachments` استفاده می‌کند؛ Contacts و Projects نیز بدون وابستگی به Tasks می‌توانند در آینده collectionهای خودشان را اضافه کنند.

## Migration policy

Migrationهای schema هر bounded context داخل همان module قرار دارند. Migration اجراشده rewrite نمی‌شود.

تغییرات schema موجود با forward migration انجام می‌شوند:

```text
legacy schema
  → create new owned schema when needed
  → backfill data
  → switch foreign keys / morph types
  → validate invariants
  → remove legacy columns/tables
```

فقط integration cleanupهایی که هم‌زمان چند bounded context حذف‌شده را جمع می‌کنند می‌توانند در `database/migrations` ریشه قرار بگیرند؛ آن‌ها مالک schema جدید نیستند.

ماژول‌های Customers، Tickets، Reports، Settings، Notification Center و Client Portal بخشی از معماری فعلی نیستند.
